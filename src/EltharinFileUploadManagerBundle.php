<?php

namespace Eltharin\FileUploadManagerBundle;

use Doctrine\ORM\EntityManagerInterface;
use Eltharin\FileUploadManagerBundle\Form\FileCollectionType;
use Eltharin\FileUploadManagerBundle\Form\FileManager\InlineFileManager;
use Eltharin\FileUploadManagerBundle\Form\FileManager\PathFileManager;
use Eltharin\FileUploadManagerBundle\Form\FileUploadType;
use Eltharin\FileUploadManagerBundle\Form\FileUploaderMapper;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\String\Slugger\SluggerInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class EltharinFileUploadManagerBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()->set('eltharin_fileuploadmanager_default__filemanager', $config['default']['file_manager']);
        $container->parameters()->set('eltharin_fileuploadmanager_default__allow_update', $config['default']['allow_update']);
        $container->parameters()->set('eltharin_fileuploadmanager_default__allow_delete', $config['default']['allow_delete']);
        $container->parameters()->set('eltharin_fileuploadmanager_default__file_storage_json', $config['default']['file_storage_json']);
        $container->parameters()->set('eltharin_fileuploadmanager_default__file_storage_path', $config['default']['file_storage_path']);
        $container->parameters()->set('eltharin_fileuploadmanager_default__delete_on_remove', $config['default']['delete_on_remove']);
        $container->parameters()->set('eltharin_fileuploadmanager_default__file_storage_inline', $config['default']['file_storage_inline']);
        $container->parameters()->set('eltharin_fileuploadmanager_default__data_class', $config['default']['data_class']);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('default')
                     ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('allow_update')->defaultValue(true )->end()
                            ->booleanNode('allow_delete')->defaultValue( true)->end()
                            ->scalarNode('data_class')->defaultValue(null )->end()
                            ->booleanNode('delete_on_remove')->defaultValue( true)->end()
                            ->booleanNode('file_storage_inline')->defaultValue(false )->end()
                            ->scalarNode('file_manager')->defaultValue(null )->end()
                            ->booleanNode('file_storage_json')->defaultValue( true)->end()
                            ->scalarNode('file_storage_path')->defaultValue( 'public' . DIRECTORY_SEPARATOR . 'files')->end()

                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

	public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
        $container->extension('twig', [
            'form_themes' => ['@EltharinFileUploadManager/file_form.html.twig']
        ]);

		$container->services()
			->set('eltharin_fileManager_locator')
			->class(ServiceLocator::class)
			->tag('container.service_locator')
			->args([
				tagged_iterator('app.fileManager', 'key'),
			])
		;

        $container->services()
            ->set(FileUploadType::class)
            ->tag('form.type')
            ->args([
                service(ParameterBagInterface::class),
                service('eltharin_fileManager_locator'),
                service(FileUploaderMapper::class),
            ])
        ;

        $container->services()
            ->set(FileCollectionType::class)
            ->tag('form.type')
            ->args([
                service('eltharin_fileManager_locator'),
                service(EntityManagerInterface::class),
            ])
        ;

		$container->services()->set(FileUploaderMapper::class);

		$container->services()->set(InlineFileManager::class)->tag('app.fileManager');
		$container->services()->set(PathFileManager::class)->tag('app.fileManager')
			->args([
				'%kernel.project_dir%',
				service(SluggerInterface::class),
			]);

	}
}
