<?php

namespace App\Filament\Resources\SiteTemplates\Pages;

use App\DTO\SiteTemplateData;
use App\Filament\Resources\SiteTemplates\SiteTemplateResource;
use App\Models\SiteTemplate;
use App\Services\Contracts\SiteTemplateServiceContract;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Компонент Filament edit site template.
 *
 * @property-read SiteTemplateServiceContract $siteTemplateService
 */
class EditSiteTemplate extends EditRecord
{
    protected static string $resource = SiteTemplateResource::class;

    protected SiteTemplateServiceContract $siteTemplateService;

    /**
     * Внедряет сервис шаблонов для admin edit.
     */
    public function boot(SiteTemplateServiceContract $siteTemplateService): void
    {
        $this->siteTemplateService = $siteTemplateService;
    }

    /**
     * Возвращает header actions.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(function (SiteTemplate $record): void {
            $this->siteTemplateService->deleteTemplate($record);
            $this->redirect(SiteTemplateResource::getUrl('index'));
        })];
    }

    /**
     * handle record update.
     *
     * @param  array<string, mixed>  $data
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var SiteTemplate $record */
        return $this->siteTemplateService->updateTemplate($record, SiteTemplateData::fromArray($data));
    }
}
