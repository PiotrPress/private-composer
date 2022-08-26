<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\Streams;

interface ProviderInterface {
    public function __construct( string $owner, string $host = '', array $auth = [] );
    public function getRepositories() : array;
    public function getReferences( Repository $repository ) : array;
    public function getPackage( Repository $repository, Reference $reference ) : ?Package;
}