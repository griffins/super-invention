<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Client;
use App\Http\Controllers\Controller;
use App\Invoice;
use App\Mail\MailMessage;
use App\Notifications\AccountConfirmation;
use App\Notifications\AccountPasswordReset;
use App\Notifications\AdminNomination;
use App\Notifications\PendingInvoice;
use App\Server;
use App\User;
use DB;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use League\ISO3166\ISO3166;
use function request;

class SupportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index($action = null)
    {
        switch ($action) {
            case 'users':
                return $this->users();
            case 'clients':
                return $this->accounts();
            default:
                return redirect(route('support', ['section' => 'users']));
        }
    }

    private function users()
    {
        $countries = collect((new ISO3166())->all())->map(function ($e) {
            return (object)$e;
        });
        $user = User::query()->findOrNew(request('user'));
        if (request()->isMethod('post')) {
            if (request('action') == 'delete') {
                $user->delete();
                $message = 'Deleted';
            } else {
                $rules = [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,id,' . $user->id,
                    'country_code' => 'required',
                    'status' => 'required',
                    'phone_number' => 'required|phone:country_code',
                ];

                request()->validate($rules);

                DB::beginTransaction();
                $user->fill(request()->only('name', 'status', 'email', 'country_code', 'phone_number'));
                $password = Str::random(6);
                $user->password = bcrypt($password);
                $user->save();

                if (request('status') == 'suspended') {
                    cache()->forever('logout_' . $user->id, true);
                }

                if ($user->wasRecentlyCreated) {
                    $user->notify(new VerifyEmail());
                    $user->notify(new AdminNomination($password));
                    $message = sprintf('User [%s] has been created.', $user->name);
                } else {
                    $message = sprintf('User [%s] has been updated.', $user->name);
                }
                DB::commit();
            }
            return redirect(route('support', ['section' => 'users']))->with('message', $message);
        } else if (request()->isMethod('GET')) {
            if (request('action') == 'edit') {
                return view('admin/users', compact('user', 'user', 'countries'));
            } else {
                $users = User::query()->get();
                return view('admin/users', compact('users', 'user', 'countries'));
            }
        }
    }

    private function accounts()
    {
        $countries = collect((new ISO3166())->all())->map(function ($e) {
            return (object)$e;
        });
        $client = Client::query()->findOrNew(request('client'));
        if (request()->isMethod('post')) {
            if (request('action') == 'delete') {
                $client->accounts()->delete();
                $client->transactions()->delete();
                $client->delete();
                $message = 'Deleted';
            } else if (request('action') == 'reset_password') {
                $password = request('password');
                $client->password = bcrypt($password);
                $client->save();
                $client->notify(new AccountPasswordReset($password));
                $message = 'Password Reset';
                \session()->put("success", "$message");
            } else {
                $rules = [
                    'name' => 'required',
                    'email' => 'required|email|unique:clients,id,' . $client->id,
                    'status' => 'required',
                    'profits' => 'required|min:0:max:100',
                    'wallet' => 'required',
                ];

                request()->validate($rules);

                DB::beginTransaction();
                $client->fill(request()->only('name', 'status', 'email', 'notes', 'wallet', 'profits'));
                $password = Str::random(6);
                $client->password = bcrypt($password);
                $client->save();

                if (request('status') == 'suspended') {
                    cache()->forever('logout_' . $client->id, true);
                }

                if ($client->wasRecentlyCreated) {
                    $client->notify(new VerifyEmail());
                    $client->notify(new AccountConfirmation($password));
                    $message = sprintf('Client [%s] has been created.', $client->name);
                } else {
                    $message = sprintf('Client [%s] has been updated.', $client->name);
                }
                DB::commit();
            }
            \session()->put("success", "$message");
            return redirect(route('support', ['section' => 'clients']))->with('message', $message);
        } else if (request()->isMethod('GET')) {
            if (request('action') == 'edit') {
                return view('admin/clients', compact('client', 'countries'));
            } else {
                $clients = Client::query()->byAdminRole()->orderBy('name')->get();
                return view('admin/clients', compact('clients', 'client', 'countries'));
            }
        }
    }

    public function mailbox()
    {
        if (request()->has('subject')) {
            $clients = Client::query()
                ->whereIn('email', json_decode(base64_decode(request('recipients'))))->pluck('email')->toArray();
            Mail::bcc($clients)->send(new MailMessage(request('subject'), request('body')));
            Session::put('success', "Messages Sent");
            return redirect()->intended();
        } else {
            if (request()->has('recipients')) {
                $recipients = request('recipients');
                return view('admin.mailbox', compact('recipients'));
            } else {
                $recipients = base64_encode(Client::query()->pluck('email')->toJson());
                return redirect(route('mailbox', compact('recipients')));
            }
        }
    }

    public function commission()
    {
        if (request()->has('account')) {
            $query = ['accounts' => base64_encode(json_encode(request('account')))];
            $query = array_merge($query, request()->except('_token', 'account'));
            return redirect(route('commission', $query));
        }
        $accounts = json_decode(base64_decode(request()->accounts));
        if (request()->isMethod('post')) {
            foreach ($accounts as $id) {
                $rates = json_decode(file_get_contents("http://api.exchangeratesapi.io/latest?base=THB"), true)['rates'];
                $account = Account::query()->findOrFail($id);
                $fx = 1 / request($account->currency, $rates[$account->currency]) * request($account->commission_currency, $rates[$account->commission_currency]);
                $invoice = new Invoice([
                    'profit' => $account->transactions()->whereBetween('closed_at', [request('from'), request('to')])->profit(),
                    'commission' => $account->commission,
                    'commission_fx' => $fx,
                    'commission_currency' => $account->commission_currency,
                    'period_start' => request('from'),
                    'period_end' => request('to')]);
                $account->invoices()->save($invoice);
                $account->client->notify(new PendingInvoice($invoice->id));
            }
            \session()->put("success", "Invoices sent");
            return redirect(route('report'))->with('message', 'Invoices Sent');
        } else {
            $rates = json_decode(file_get_contents("http://api.exchangeratesapi.io/latest?base=THB"), true)['rates'];
            $currencies = Account::query()->select('commission_currency')->distinct()->whereIn('id', $accounts)->pluck('commission_currency');
            $currencies = $currencies
                ->merge(Account::query()->select('currency')->distinct('currency')
                    ->whereIn('id', $accounts)->pluck('currency'))->reject(function ($a) {
                    return $a == "THB";
                })->unique();
            return view('admin.invoices', compact('rates', 'currencies'));
        }
    }
}
