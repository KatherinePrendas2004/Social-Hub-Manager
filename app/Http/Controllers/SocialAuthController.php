<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SocialOAuthService;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Auth;

class SocialAuthController extends Controller
{
    protected $oauth;

    public function __construct(SocialOAuthService $oauth)
    {
        $this->oauth = $oauth;
    }

    public function index()
    {
        $accounts = Auth::user()->socialAccounts()->get();
        return view('social.index', compact('accounts'));
    }

    public function redirectToProvider($platform)
    {
        $url = $this->oauth->getAuthUrl($platform);
        return redirect()->away($url);
    }

    public function handleProviderCallback(Request $request, $platform)
    {
        if ($request->has('error')) {
            return redirect()->route('social.index')->with('error', 'Autorización cancelada.');
        }

        if (!$request->has('code')) {
            return redirect()->route('social.index')->with('error', 'Código de autorización no recibido.');
        }

        $ok = $this->oauth->handleCallback($platform, $request->code);

        if (!$ok) {
            return redirect()->route('social.index')->with('error', ucfirst($platform) . ' fallo al conectar.');
        }

        return redirect()->route('social.index')->with('success', ucfirst($platform) . ' conectado correctamente.');
    }

    // Opcional: desconectar
    public function disconnect($platform)
    {
        $account = Auth::user()->socialAccounts()->where('platform', $platform)->first();
        if ($account) {
            $account->update([
                'is_active' => false,
                'disconnected_at' => now(),
                'access_token' => null,
                'refresh_token' => null,
            ]);
        }
        return redirect()->route('social.index')->with('success', ucfirst($platform) . ' desconectado.');
    }
}
