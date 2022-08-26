<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\Streams;

use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;

class Plugin implements PluginInterface {
    public function activate( Composer $composer, IOInterface $io ) : void {
        Stream::setComposer( $composer );
        Stream::setIO( $io );
        Stream::register( 'github' );
        Stream::register( 'bitbucket' );
    }

    public function deactivate( Composer $composer, IOInterface $io ) : void {}
    public function uninstall( Composer $composer, IOInterface $io ) : void {}
}