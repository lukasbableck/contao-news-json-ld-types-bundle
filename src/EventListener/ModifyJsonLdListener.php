<?php
namespace Lukasbableck\ContaoNewsJsonLdTypesBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ResponseContext\JsonLd\JsonLdManager;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\ModuleModel;
use Contao\ModuleNews;
use Contao\NewsModel;
use Spatie\SchemaOrg\NewsArticle;

class ModifyJsonLdListener {
    public function __construct(
        private readonly ResponseContextAccessor $responseContextAccessor,
    ) {
    }

    #[AsHook('getFrontendModule')]
    public function onGetFrontendModule(ModuleModel $model, string $buffer, object $module): string {
        if (!($module instanceof ModuleNews)) {
            return $buffer;
        }

        $jsonldManager = $this->responseContextAccessor->getResponseContext()->get(JsonLdManager::class);
        $graph = $jsonldManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG);
        if (!($graph->getNodes()[NewsArticle::class] ?? false)) {
            return $buffer;
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

        return $buffer;
    }
}
