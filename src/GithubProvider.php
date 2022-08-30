<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\Streams;

use PiotrPress\Remoter\Url;
use PiotrPress\Remoter\Header;
use PiotrPress\Remoter\Request;
use PiotrPress\Remoter\Response;

class GithubProvider implements ProviderInterface {
    protected string $owner = '';
    protected string $host = 'github.com';
    protected array $auth = [];

    public function __construct( string $owner, string $host = '', array $auth = [] ) {
        $this->owner = $owner;
        if ( $host ) $this->host = $host;
        if ( $auth ) $this->auth = $auth;
    }

    public function getRepositories() : array {
        $repositories = [];

        $url = $this->prepareUrl( "/search/repositories?q=user:{$this->owner}&per_page=100" );
        foreach ( $this->getContents( $url ) as $content )
            foreach ( $content[ 'items' ] ?? [] as $repository )
                $repositories[] = ( new Repository() )
                    ->setName( $repository[ 'name' ] );

        return $repositories;
    }

    public function getReferences( Repository $repository ) : array {
        $references = [];

        $url = $this->prepareUrl( "/repos/{$this->owner}/{$repository->getName()}/git/matching-refs/" );
        foreach ( $this->getContents( $url ) as $content )
            foreach ( $content as $reference ) {
                list( , $type, $name ) = \array_merge( \explode( '/', $reference[ 'ref' ] ), [ '' ] );
                if ( \in_array( $type, [ 'heads', 'tags' ] ) )
                    $references[] = ( new Reference() )
                        ->setName( $name )
                        ->setType( $type === 'heads' ? 'branch' : 'tag' )
                        ->setHash( $reference[ 'object' ][ 'sha' ] );
            }

        return $references;
    }

    public function getPackage( Repository $repository, Reference $reference ) : ?Package {
        $url = $this->prepareUrl( "/repos/{$this->owner}/{$repository->getName()}/contents/composer.json?ref={$reference->getHash()}" );

        if ( ! $content = \reset( $this->getContents( $url ) ) ) return null;

        $content[ 'dist' ] = $this->prepareDist( $repository, $reference );
        $content[ 'source' ] = $this->prepareSource( $repository, $reference );

        return new Package( $repository, $reference, $content );
    }

    protected function prepareDist( Repository $repository, Reference $reference ) {
        return [
            'type' => 'zip',
            'url' => (string)$this->prepareUrl( "/repos/{$this->owner}/{$repository->getName()}/zipball/{$reference->getHash()}" )
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
        $url = 'github.com' === $this->host ? "api.{$this->host}" : "{$this->host}/api/v3";
        return new Url( "https://{$url}{$path}" );
    }

    protected function prepareAuth() : string {
        return $this->auth[ $this->host ][ 'password' ] ?? '' ?
            \base64_encode( ( $this->auth[ $this->host ][ 'username' ] ?? 'x-oauth-basic' ) . ':' . $this->auth[ $this->host ][ 'password' ] ) : '';
    }

    protected function prepareHeader() : Header {
        $header = new Header( [
            'Accept' => 'application/vnd.github.v3.raw+json',
            'User-Agent' => 'Composer'
        ], true );

        if ( $auth = $this->prepareAuth() )
            $header->set( 'Authorization', 'Basic ' . $auth );

        return $header;
    }

    protected function getNextUrl( Header $header ) : ?Url {
        foreach ( \explode( ',', $header->get( 'link' ) ) as $link )
            if ( \preg_match('{<(.+?)>; *rel="next"}', $link, $match ) )
                return new Url( $match[ 1 ] );

        return null;
    }

    protected function getContents( Url $url ) : array {
        $contents = [];

        while ( (bool)$url ) {
            $response = ( new Request( $url, 'GET', $this->prepareHeader() ) )->send();

            if ( 200 === (int)$response->getHeader()->get( 'code' ) and $content = \json_decode( $response->getContent(), true ) )
                $contents[] = $content;

            $url = $this->getNextUrl( $response->getHeader() );
        }

        return $contents;
    }
}