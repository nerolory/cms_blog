<?php

namespace App\Filament\Resources\SiteTemplates\Pages;

use App\DTO\SiteTemplateData;
use App\Filament\Resources\SiteTemplates\SiteTemplateResource;
use App\Services\Contracts\SiteTemplateServiceContract;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Компонент Filament create site template.
 *
 * @property-read SiteTemplateServiceContract $siteTemplateService
 */
class CreateSiteTemplate extends CreateRecord
{
    protected static string $resource = SiteTemplateResource::class;

    protected SiteTemplateServiceContract $siteTemplateService;

    /**
     * Внедряет сервис шаблонов для admin create.
     */
    public function boot(SiteTemplateServiceContract $siteTemplateService): void
    {
        $this->siteTemplateService = $siteTemplateService;
    }

    /**
     * handle record creation.
     *
     * @param  array<string, mixed>  $data
     * @return Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        return $this->siteTemplateService->createTemplate(SiteTemplateData::fromArray($data));
    }
}
