<?php
namespace Lukasbableck\ContaoNewsJsonLdTypesBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\LayoutModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\PageRegular;
use Spatie\SchemaOrg\NewsArticle;

#[AsHook('generatePage')]
class ModifyJsonLdListener {
    public function __construct(
        private readonly ResponseContextAccessor $responseContextAccessor,
    ) {
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void {
        $jsonldManager = $this->responseContextAccessor->getResponseContext()->get(JsonLdManager::class);
        $graph = $jsonldManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG);
        if (!($graph->getNodes()[NewsArticle::class] ?? false)) {
            return;
        }

        $newsArticles = $graph->getNodes()[NewsArticle::class];
        foreach ($newsArticles as $key => $newsArticle) {
            $identifier = $newsArticle->getProperties()['identifier'] ?? null;
            if (!$identifier) {
                continue;
            }

            $newsModel = NewsModel::findByPk(end(explode('/', $identifier)));
            if (!$newsModel) {
                continue;
            }

            $newsArchive = $newsModel->getRelated('pid');
            if (!$newsArchive) {
                continue;
            }

            $jsonLdType = $newsArchive->jsonLdType ?: 'NewsArticle';
            if ('NewsArticle' === $jsonLdType) {
                continue;
            }

            $newSchemaClass = 'Spatie\SchemaOrg\\'.$jsonLdType;
            if (!class_exists($newSchemaClass)) {
                continue;
            }

            $newSchemaInstance = new $newSchemaClass();
            foreach ($newsArticle->getProperties() as $key => $value) {
                $newSchemaInstance->setProperty($key, $value);
            }
            $graph->add($newSchemaInstance);
            $graph->hide(NewsArticle::class, $identifier);
        }
    }
}
