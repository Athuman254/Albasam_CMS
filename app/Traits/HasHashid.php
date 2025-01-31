<?php

namespace App\Traits;

use App\Scope\HashidScope;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Model|null findByHashid($hashid)
 * @method Model findByHashidOrFail($hashid)
 */
trait HasHashid
{
    public static function bootHasHashid()
    {
        static::addGlobalScope(new HashidScope);
    }

    public function hashid()
    {
        return $this->idToHashid($this->getKey());
    }

    /**
     * Decode the hashid to the id
     *
     * @param string $hashid
     * @return int|null
     */
    public function hashidToId($hashid)
    {
        $hashids = new Hashids('kwawasco_hash', 16);

        return $hashids->decode($hashid)[0];

        //        return (new Hashids())->decode($hashid)[0];

        //        return @Hashids::decode($hashid)[0];
    }

    /**
     * Encode an id to its equivalent hashid
     *
     * @param string $id
     * @return string|null
     */
    public function idToHashid($id)
    {
        $hashids = new Hashids('kwawasco_hash', 16);

        return @$hashids->encode($id);
    }

    public function getHashidsConnection()
    {
        return config('hashids.default');
    }

    protected function getHashidAttribute()
    {
        return $this->hashid();
    }
}
