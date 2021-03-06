<?php

namespace MauticPlugin\MauticRecommenderBundle\Command;

use Mautic\CoreBundle\Translation\Translator;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticRecommenderBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecommenderBundle\Api\Service\ApiUserItemsInteractions;
use MauticPlugin\MauticRecommenderBundle\Events\Processor;
use MauticPlugin\MauticRecommenderBundle\Helper\RecommenderHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PushDataToRecommenderCommand extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var array
     */
    private $types = ['events', 'items'];

    private $actions = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mautic:recommender:import')
            ->setDescription('Import data to Recommender')
            ->addOption(
                '--type',
                '-t',
                InputOption::VALUE_REQUIRED,
                'Type options: '.implode(', ', $this->getTypes()),
                null
            )->addOption(
                '--file',
                '-f',
                InputOption::VALUE_OPTIONAL,
                'JSON file to import for types for '.implode(', ', $this->getActions())
            );
        $this->addOption(
            '--batch-limit',
            '-l',
            InputOption::VALUE_OPTIONAL,
            'Set batch size of contacts to process per round. Defaults to 100.',
            100
        );

        $this->addOption(
            '--timeout',
            null,
            InputOption::VALUE_OPTIONAL,
            'Set delay to ignore item to update. Default -1 day.',
            '-1 day'
        );


        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var IntegrationHelper $integrationHelper */
        $integrationHelper = $this->getContainer()->get('mautic.helper.integration');
        $integrationObject = $integrationHelper->getIntegrationObject('Recommender');
        /** @var Translator $translator */
        $translator = $this->getContainer()->get('translator');

        if (!$integrationObject->getIntegrationSettings()->getIsPublished()) {
            return $output->writeln('<info>'.$translator->trans('mautic.plugin.recommender.disabled').'</info>');
        }

        /** @var RecommenderHelper $recommenderHelper */
        $recommenderHelper = $this->getContainer()->get('mautic.recommender.helper');

        $type = $input->getOption('type');

        if (empty($type)) {
            return $output->writeln(
                sprintf(
                    '<error>ERROR:</error> <info>'.$translator->trans(
                        'mautic.plugin.recommender.command.type.required',
                        ['%types' => implode(', ', $this->getTypes())]
                    ).'</info>'
                )
            );
        }

        if (!in_array($type, $this->getTypes())) {
            return $output->writeln(
                sprintf(
                    '<error>ERROR:</error> <info>'.$translator->trans(
                        'mautic.plugin.recommender.command.bad.type',
                        ['%type' => $type, '%types' => implode(', ', $this->getTypes())]
                    ).'</info>'
                )
            );
        }

        $file = $input->getOption('file');


        if (!in_array($type, $this->getTypes()) && empty($file)) {
            return $output->writeln(
                sprintf(
                    '<error>ERROR:</error> <info>'.$translator->trans(
                        'mautic.plugin.recommender.command.option.required',
                        ['%file' => 'file', '%actions' => implode(', ', $this->getActions())]
                    )
                )
            );
        }

        if ($type !== 'contacts') {
            if (empty($file)) {
                return $output->writeln(
                    sprintf(
                        '<error>ERROR:</error> <info>'.$translator->trans(
                            'mautic.plugin.recommender.command.file.required'
                        )
                    )
                );
            }

            if (empty(!file_exists($file))) {
                return $output->writeln(
                    sprintf(
                        '<error>ERROR:</error> <info>'.$translator->trans(
                            'mautic.plugin.recommender.command.file.fail',
                            ['%file' => $file]
                        )
                    )
                );
            }
            $items = \JsonMachine\JsonMachine::fromFile($file);

            if (empty($items) || ![$items]) {
                return $output->writeln(
                    sprintf(
                        '<error>ERROR:</error> <info>'.$translator->trans(
                            'mautic.plugin.recommender.command.json.fail',
                            ['%file' => $file]
                        )
                    )
                );
            }
        }


        /** @var ApiCommands $apiCommands */
        $apiCommands = $this->getContainer()->get('mautic.recommender.service.api.commands');

        switch ($type) {
            case "items":
                $apiCommands->ImportItems($items, $input->getOption('batch-limit'), $input->getOption('timeout'), $output);
                break;
            case "events":
                /** @var Processor $eventProcessor */
                $eventProcessor = $this->getContainer()->get('mautic.recommender.events.processor');
                $counter        = 0;
                foreach ($items as $item) {
                    try {
                        $eventProcessor->process($item);
                        $counter++;
                    } catch (\Exception $e) {
                        $output->writeln($e->getMessage());
                    }
                }
                $output->writeln('Imported '.$counter.' events');
                break;
        }
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return array_merge($this->types, $this->actions);
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }
}
