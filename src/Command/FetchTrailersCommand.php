<?php

namespace App\Command;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class FetchTrailersCommand extends Command
{
    private const SOURCE = 'https://trailers.apple.com/trailers/home/rss/newtrailers.rss';

    protected static $defaultName = 'app:fetch:trailers';
    protected static $defaultDescription = 'Add a short description for your command';
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;
    private array $header;
    private int $itemCnt;
    private LoggerInterface $logger;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $em,  LoggerInterface $logger, ValidatorInterface $validator, $name = null)
    {
        parent::__construct($name);
        $this->em = $em;
        $this->itemCnt = 10;
        $this->header = [
            'Accept-Language'=>['en'],
            'Content-Type' => ['text/html','application/xhtml+xml','application/xml;q=0.9','image/avif','image/webp','image/apng','*/*;q=0.8','application/signed-exchange;v=b3;q=0.9'],
            'Accept'=>['application/json'],
            'charset'=>['utf-8']
        ];
        $this->logger = $logger;
        $this->validator = $validator;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('itemCnt', InputArgument::OPTIONAL, 'Overwrite crawler items cont, default 10.')
            ->addArgument('source', InputArgument::OPTIONAL, 'Overwrite source.')
            ->setDescription('Fetch data from iTunes Movie Trailers')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write(sprintf('Start %s at %s', __CLASS__, (string) date_create()->format(DATE_ATOM)));
        $source = self::SOURCE;
        if ($input->getArgument('source')) {
            $source = $input->getArgument('source');
        }

        $itemCnt = $this->itemCnt;
        if ($input->getArgument('itemCnt')) {
            $itemCnt = $input->getArgument('itemCnt');
        }

        is_int($itemCnt) ? $this->itemCnt = $itemCnt: '';

        if (!is_string($source)) {
            throw new RuntimeException('Source must be string');
        }

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Fetch data from %s', $source));

        try {

            $httpClient = SymfonyHttpClient::create(['headers'=>$this->header]);
            // make a request
            $response = $httpClient->request('GET', $source);

        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException($e->getMessage());
        }
        if (($status = $response->getStatusCode()) !== 200) {
            throw new RuntimeException(sprintf('Response status is %d, expected %d', $status, 200));
        }
        $data = $response->getContent();

        $this->processXml($data);

        $this->logger->info(sprintf('End %s at %s', __CLASS__, (string) date_create()->format(DATE_ATOM)));

        return Command::SUCCESS;
    }

    /**
     * @param string $data
     * @throws \Exception
     */
    protected function processXml(string $data): void
    {
        $xml = (new \SimpleXMLElement($data))->children();

        if (!property_exists($xml, 'channel')) {
            throw new RuntimeException('Could not find \'channel\' element in feed');
        }

        $inter = 0;
        foreach ($xml->channel->item as $item) {

            $namespace = $item->getNamespaces(true)['content'];

            $trailer = $this->getMovie((string) $item->title)
                ->setTitle((string) $item->title)
                ->setDescription((string) $item->description)
                ->setLink((string) $item->link)
                ->setPubDate($this->parseDate((string) $item->pubDate))
                ->setImage($this->parseImage((string)$item->children($namespace)->encoded))
            ;

            $errors = $this->validator->validate($trailer, null, ['movie-add']);

            if(count($errors) >0) {

                foreach ($errors as $error) {

                    $this->logger->error('INVALID_DATA', [sprintf('On the property %s validation error, message is: %s', $error->getPropertyPath(), $error->getMessage())]);
                }
                continue;
            }

            $this->em->persist($trailer);
            $inter++;

            if($inter === $this->itemCnt) {
                break;
            }

        }

        $this->em->flush();
    }

    protected function getMovie(string $title): Movie
    {
        $item = $this->em->getRepository(Movie::class)->findOneBy(['title' => $title]);

        if ($item === null) {
            $this->logger->info('Create new Movie', ['title' => $title]);
            $item = new Movie();
        } else {
            $this->logger->info('Move found', ['title' => $title]);
        }

        if (!($item instanceof Movie)) {
            throw new RuntimeException('Wrong type!');
        }

        return $item;
    }

    protected function parseDate(string $date): \DateTime
    {
        return new \DateTime($date);
    }

    protected function parseImage(string $date): ?string
    {
        $crawler = new Crawler($date);
        $image = $crawler->filter('img')->first();
        if($image->count()) {
            return $image->image()->getUri();
        }
        return null;
    }
}
