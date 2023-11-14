<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\Streams;

use PiotrPress\Remoter\Url;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Command\BaseCommand;

class ComposerCommand extends BaseCommand {
    protected function configure() : void {
        $this
            ->setName( 'stream-dump' )
            ->setDescription( '[stream] Dump the contents of github/bitbucket stream.' )
            ->addArgument( 'stream', InputArgument::REQUIRED, 'Stream to dump: <github|bitbucket>://<owner>[@<host>' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) : int {
        if ( ! \in_array( ( new Url( $stream = $input->getArgument( 'stream' ) . '/packages.json' ) )->getScheme(), [ 'github', 'bitbucket' ] ) )
            throw new \InvalidArgumentException( 'Stream must be either github:// or bitbucket://' );

        $output->write( \file_get_contents( $stream ) );

        return self::SUCCESS;
    }
}