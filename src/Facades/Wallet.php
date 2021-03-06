<?php

namespace ManeOlawale\Walletable\Facades;

use Illuminate\Support\Facades\Facade;
use ManeOlawale\Walletable\WalletRepository;

/**
 * @method static \Intervention\Image\Image make(mixed $data)
 * @method static self configure(array $config)
 * @method static \Intervention\Image\Image canvas(int $width, int $height, mixed $background = null)
 * @method static \Intervention\Image\Image cache(\Closure $callback, int $lifetime = null, boolean $returnObj = false)
 */
class Wallet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return WalletRepository::class;
    }
}
