<?php

namespace ManeOlawale\Walletable;
use Illuminate\Database\Eloquent\Model;

class WalletRepository
{
    private $app;

    private $providers;
    
    public function __construct()
    {
        $this->app = app();
        $this->registerBaseProviders();
    }


    public function make( Models\WalletInterface $wallet)
    {
        $providerKey = (property_exists($wallet, 'providerKey'))? $wallet->providerKey : 'provider';

        return $this->makeProvider($wallet->provider, $wallet);
    }

    public function makeProvider( string $provider, Models\WalletInterface $wallet) : Providers\ProviderInterface
    {
        return (isset($this->prividers[$provider]))? new $this->providers[$provider]($wallet) : new $this->providers[config('wallet.default')]($wallet);
    }

    public function registerBaseProviders()
    {
        $this->provider(Providers\DatabaseProvider::class);
        $this->provider(Providers\UnknownProvider::class);
        return $this;
    }

    public function provider(string $class)
    {
        if ( !(class_exists($class) && is_subclass_of($class, Providers\ProviderAbstract::class)) ) throw new \Exception("Invalid Provider class [$class]");
        
        $this->providers[$class::signature()] = $class;
    }

    public function generate(string $provider, Models\Walletable $owner, string $label = null, string $name = null)
    {
        if (!$label) $label = config('wallet.generation.label', 'wallet');
        if (!$name) $name = config('wallet.generation.name', 'Wallet');
        $owner_id = $owner->{$owner->getKeyName()};
        $owner_type = get_class($owner);
        $providerClass = (isset($this->providers[$provider])) ? $this->providers[$provider]: $this->providers['unknown'];
        $model = config('walletable.models.wallet');
        $wallet = (new $model)->fill(
            [
                'owner_id' => $owner_id,
                'owner_type' => $owner_type,
                'label' => $label,
                'name' => $name,
                'provider' => $providerClass::signature(),
                'balance' => 0,
                'data' => '{}',
            ]
        );

        $i = 1;
        while ($i <= config('wallet.generation.tries', 5)) {

            $result = $providerClass::generate($owner, $wallet);

            if ($result['success']) {
                break;
            }

        }

        if ($result['success']) {
            $wallet->fill(
                $result['data']
            )->save();
            return $this->make($wallet);
        }else{
            return false;
        }

    }


}
