<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\Streams;

use PiotrPress\Remoter\Url;
use PiotrPress\Remoter\Header;
use PiotrPress\Remoter\Request;
use PiotrPress\Remoter\Response;

class BitbucketProvider implements ProviderInterface {
    protected string $owner = '';
    protected string $host = 'bitbucket.org';
    protected array $auth = [];

    public function __construct( string $owner, string $host = '', array $auth = [] ) {
        $this->owner = $owner;
        if ( $host ) $this->host = $host;
        if ( $auth ) $this->auth = $auth;
    }

    public function getRepositories() : array {
        $repositories = [];

        $url = $this->prepareUrl( "/repositories/{$this->owner}" );
        foreach ( $this->getContents( $url ) as $content )
            foreach ( $content[ 'values' ] ?? [] as $repository )
                $repositories[] = ( new Repository() )
                    ->setName( $repository[ 'slug' ] );

        return $repositories;
    }

    public function getReferences( Repository $repository ) : array {
        $references = [];

        $url = $this->prepareUrl( "/repositories/{$this->owner}/{$repository->getName()}/refs" );
        foreach ( $this->getContents( $url ) as $content )
            foreach ( $content[ 'values' ] ?? [] as $reference )
                if ( \in_array( $reference[ 'type' ], [ 'branch', 'tag' ] ) )
                    $references[] = ( new Reference() )
                        ->setName( $reference[ 'name' ] )
                        ->setType( $reference[ 'type' ] )
                        ->setHash( $reference[ 'target' ][ 'hash' ] );

        return $references;
    }

    public function getPackage( Repository $repository, Reference $reference ) : ?Package {
        $url = $this->prepareUrl( "/repositories/{$this->owner}/{$repository->getName()}/src/{$reference->getHash()}/composer.json" );

        $array = $this->getContents( $url );
        if ( ! $content = \reset( $array ) ) return null;

        $content[ 'dist' ] = $this->prepareDist( $repository, $reference );
        $content[ 'source' ] = $this->prepareSource( $repository, $reference );

        return new Package( $repository, $reference, $content );
    }

    protected function prepareDist( Repository $repository, Reference $reference ) {
        return [
            'type' => 'zip',
            'url' => "https://{$this->host}/{$this->owner}/{$repository->getName()}/get/{$reference->getHash()}.zip"
        ];
    }

    protected function prepareSource( Repository $repository, Reference $reference ) {
        return [
            'type' => 'git',
            'url' => "git@{$this->host}:{$this->owner}/{$repository->getName()}.git",
            'reference' => $reference->getHash()
        ];
    }

    protected function prepareUrl( string $path ) : Url {
        $url = 'bitbucket.org' === $this->host ? "api.{$this->host}" : $this->host;
        return new Url( "https://{$url}/2.0{$path}" );
    }

    protected function prepareAuth() : string {
        return ( $this->auth[ $this->host ][ 'username' ] ?? '' and $this->auth[ $this->host ][ 'password' ] ?? '' ) ?
            \base64_encode( $this->auth[ $this->host ][ 'username' ] . ':' . $this->auth[ $this->host ][ 'password' ] ) : '';
    }

    protected function prepareHeader() : Header {
        $header = new Header( [
            'Accept' => 'application/json',
            'User-Agent' => 'Composer'
        ], true );

        if ( $auth = $this->prepareAuth() )
            $header->set( 'Authorization', 'Basic ' . $auth );

        return $header;
    }

    protected function getNextUrl( array $content ) : ?Url {
        if ( $content[ 'next' ] ?? '' ) return new Url( $content[ 'next' ] );

        return null;
    }

    protected function getContents( Url $url ) : array {
        $contents = [];

        while ( (bool)$url ) {
            $response = ( new Request( $url, 'GET', $this->prepareHeader() ) )->send();

            if ( 200 === (int)$response->getHeader()->get( 'code' ) and $content = \json_decode( $response->getContent(), true ) )
                $contents[] = $content;

            $url = $this->getNextUrl( $content ?? [] );
        }

        return $contents;
    }
}