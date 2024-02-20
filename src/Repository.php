<?php declare( strict_types = 1 );

namespace PiotrPress\PrivateComposer;

class Repository {
    protected string $name = '';
    
    public function setName( string $name ) : self {
        $this->name = $name;
        return $this;
    }

    public function getName() : string {
        return $this->name;
    }
}