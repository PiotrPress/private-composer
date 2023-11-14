<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\Streams;

use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Composer;
use Composer\IO\IOInterface;

class Plugin implements PluginInterface, Capable, CommandProvider {
    public function activate( Composer $composer, IOInterface $io ) : void {
        Stream::setComposer( $composer );
        Stream::setIO( $io );
        Stream::register( 'github' );
        Stream::register( 'bitbucket' );
    }

    public function deactivate( Composer $composer, IOInterface $io ) : void {}
    public function uninstall( Composer $composer, IOInterface $io ) : void {}

    public function getCapabilities() : array {
        return [ 'Composer\Plugin\Capability\CommandProvider' => __CLASS__ ];
    }

    public function getCommands() : array {
        return [ new ComposerCommand() ];
    }
}