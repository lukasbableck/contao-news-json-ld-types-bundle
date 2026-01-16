<?php
namespace Lukasbableck\ContaoNewsJsonLdTypesBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use Lukasbableck\ContaoNewsJsonLdTypesBundle\ContaoNewsJsonLdTypesBundle;

class Plugin implements BundlePluginInterface {
    public function getBundles(ParserInterface $parser): array {
        return [BundleConfig::create(ContaoNewsJsonLdTypesBundle::class)->setLoadAfter([ContaoCoreBundle::class, ContaoNewsBundle::class])];
    }
}
