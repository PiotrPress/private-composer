<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\Streams;

use Composer\IO\IOInterface;

class ComposerRepository {
    protected IOInterface $io;
    protected ProviderInterface $provider;

    public function __construct( IOInterface $io, ProviderInterface $provider ) {
        $this->io = $io;
        $this->provider = $provider;
    }

    public function getPackages() : string {
        $packages = [];

        $repositories = $this->provider->getRepositories();

        $progressBar = $this->io->getProgressBar();
        $progressBar->start( \count( $repositories ) );

        foreach ( $repositories as $repository ) {
            foreach ( $this->provider->getReferences( $repository ) as $reference )
                if ( $package = $this->provider->getPackage( $repository, $reference ) and $package->getName() )
                    $packages[ $package->getName() ][ $package->getVersion() ] = $package->toArray();
            $progressBar->advance();
        }

        $progressBar->finish();
        $progressBar->clear();

        return (string)\json_encode( [
            'info' => \count( $packages ) . ' packages',
            'packages' => $packages
        ], \JSON_THROW_ON_ERROR );
    }
}