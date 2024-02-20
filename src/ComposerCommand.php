<?php declare( strict_types = 1 );

namespace PiotrPress\PrivateComposer;

use PiotrPress\Remoter\Url;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Command\BaseCommand;

class ComposerCommand extends BaseCommand {
    protected function configure() : void {
        $this
            ->setName( 'packages' )
            ->setDescription( '[packages] Dump the contents from github/bitbucket URL.' )
            ->addArgument( 'url', InputArgument::REQUIRED, 'Packages from URL: <github|bitbucket>://<owner>[@<host> to dump.' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) : int {
        if ( ! \in_array( ( new Url( $url = $input->getArgument( 'packages' ) . '/packages.json' ) )->getScheme(), [ 'github', 'bitbucket' ] ) )
            throw new \InvalidArgumentException( 'URL must be either github:// or bitbucket:// protocol' );

        $output->write( \file_get_contents( $url ) );

        return self::SUCCESS;
    }
}