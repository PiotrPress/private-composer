<?php declare( strict_types = 1 );

namespace PiotrPress\PrivateComposer;

class Package {
    protected Repository $repository;
    protected Reference $reference;
    protected array $content = [];

    public function __construct( Repository $repository, Reference $reference, array $content ) {
        $this->repository = $repository;
        $this->reference = $reference;
        $this->content = $content;

        $this->content[ 'version' ] = 'branch' === $reference->getType() ? 'dev-' . $reference->getName() : $reference->getName();
    }

    public function getName() : string {
        return $this->content[ 'name' ] ?? '';
    }

    public function getVersion() : string {
        return $this->content[ 'version' ] ?? '';
    }

    public function toArray() : array {
        return $this->content;
    }
}