<?php declare( strict_types = 1 );

namespace PiotrPress\PrivateComposer;

use PiotrPress\Streamer;
use PiotrPress\Remoter\Url;

use Composer\Composer;
use Composer\IO\IOInterface;

class Stream extends Streamer {
    static protected ?Composer $composer = null;
    static protected ?IOInterface $io = null;

    protected ?Url $url = null;

    static public function setComposer( Composer $composer ) : void {
        self::$composer = $composer;
    }

    static public function setIO( IOInterface $io ) : void {
        self::$io = $io;
    }

    static public function register( string $protocol, int $flags = 0 ) : bool {
        if ( \in_array( $protocol, \stream_get_wrappers() ) ) self::unregister( $protocol );
        return parent::register( $protocol, $flags );
    }

    public function stream_open( string $path, string $mode, int $options, ?string &$opened_path ) : bool {
        $this->url = new Url( \dirname( $path ) );

        $composerRepository = new ComposerRepository( self::$io, $this->getProvider() );
        self::$data[ $path ] = $composerRepository->getPackages();

        return parent::stream_open( $path, $mode, $options, $opened_path );
    }

    protected function getProvider() : ProviderInterface {
        $provider = __NAMESPACE__ . '\\' . \ucfirst( $this->url->getScheme() ) . 'Provider';
        return new $provider( $this->getOwner(), $this->getHost(), $this->getAuth() );
    }

    protected function getOwner() : string {
        return \strpos( (string)$this->url, '@' ) ? $this->url->getUser() : $this->url->getHost();
    }

    protected function getHost() : string {
        return \strpos( (string)$this->url, '@' ) ? $this->url->getHost() : '';
    }

    protected function getAuth() : array {
        return self::$composer->getConfig()->get( 'http-basic' ) ?? [];
    }
}