<?php declare( strict_types = 1 );

namespace PiotrPress\PrivateComposer;

class Reference {
    protected string $name = '';
    protected string $type = '';
    protected string $hash = '';

    public function setName( string $name ) : self {
        $this->name = $name;
        return $this;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setType( string $type ) : self {
        $this->type = $type;
        return $this;
    }

    public function getType() : string {
        return $this->type;
    }

    public function setHash( string $hash ) : self {
        $this->hash = $hash;
        return $this;
    }

    public function getHash() : string {
        return $this->hash;
    }
}