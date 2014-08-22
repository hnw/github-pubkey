<?php
namespace Hnw\GithubPubkey\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

use GuzzleHttp\Client;
use GuzzleHttp\Event\CompleteEvent;

use Hnw\SingleSubcommandConsole\Command\Command;

class GithubPubkeyCommand extends Command
{
    protected function configure()
    {   
        $this->setName('github-pubkey')
             ->setDescription("Get GitHub Pubkeys")
             ->setDefinition(array(
                new InputOption('from', 's', InputOption::VALUE_REQUIRED, 'Start number of the range of GitHub user ID'),
                new InputOption('to', 'e', InputOption::VALUE_REQUIRED, 'Stop number of the range of GitHub user ID'),
                new InputOption('num', null, InputOption::VALUE_REQUIRED, 'Maximum number of login IDs to display'),
                new InputOption('token', 't', InputOption::VALUE_REQUIRED, 'Specify GitHub personal access token'),
                new InputOption('search', null, InputOption::VALUE_REQUIRED, 'Search login name'),
                new InputOption('with-user-id', null, InputOption::VALUE_NONE, 'Display login ID with user ID'),
                new InputOption('with-http-cache', null, InputOption::VALUE_NONE, 'HTTP Access using local cache'),

            ))
            ;
    }

    /**
     * コマンドラインオプションの検査を行う
     *
     * 問題がなければ　true　を返す。
     * 問題があれば \InvalidArgumentException をスローする。
     */
    protected function validate(InputInterface $input)
    {
        $startId = $input->getOption('from');
        $stopId = $input->getOption('to');
        $maxUser  = $input->getOption('num');

        if (isset($startId) && !preg_match('/^[+-]?[0-9]+$/', $startId)) {
            throw new \InvalidArgumentException('"from" number must be integer');
        }
        if (isset($stopId) && !preg_match('/^[+-]?[0-9]+$/', $stopId)) {
            throw new \InvalidArgumentException('"to" number must be integer');
        }
        if (isset($maxUser) && !preg_match('/^[+-]?[0-9]+$/', $maxUser)) {
            throw new \InvalidArgumentException('"num" number must be integer');
        }
        if ($startId < 0) {
            throw new \InvalidArgumentException('"from" number must be positive');
        }
        if ($stopId < 0) {
            throw new \InvalidArgumentException('"to" number must be positive');
        }
        if ($maxUser < 0) {
            throw new \InvalidArgumentException('"num" number must be positive');
        }
        if ($stopId > 0 && $startId >= $stopId) {
           throw new \InvalidArgumentException('"to" number must be greater than "from" number');
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        sleep(10);
        $client = new Client();
        $client->sendAll($this->requestsGenerator($client), [
                'parallel' => 4,
                'complete' => function (CompleteEvent $event) {
                    echo 'Completed request to ' . $event->getRequest()->getUrl() . "\n";
                    echo 'Response: ' . $event->getResponse()->getBody() . "\n\n";
                }
            ]);
    }

    protected function requestsGenerator($client)
    {
        $loginIds = [
            'mojombo',
            'defunkt',
            'pjhyett',
            'wycats',
            'ezmobius',
            'ivey',
            'evanphx',
            'vanpelt',
            'wayneeseguin',
            'brynary',
        ];
        foreach ($loginIds as $loginId) {
            yield $client->createRequest('GET', 'https://github.com/'.$loginId.'.keys');
        }
    }

}
