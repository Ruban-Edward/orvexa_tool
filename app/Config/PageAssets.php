<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Heavy front-end libraries that only some pages need. They are loaded in the
 * <head> of the views listed here instead of on every page.
 *
 * A view rendered through BaseController::template_view() is matched by its
 * view name (e.g. 'sprint/createSprint').
 */
class PageAssets extends BaseConfig
{
    /**
     * Library bundles, as script paths relative to the public directory, in load order.
     *
     * @var array<string, list<string>>
     */
    public array $libraries = [
        'charts'   => [
            'support/extensions/chart.js/chart.umd.js',
            'support/extensions/chart.js/chartjs-plugin-datalabels.js',
        ],
        'editor'   => [
            'support/extensions/tinymce/tinymce.min.js',
            'support/static/js/pages/tinymce.js',
        ],
        'excel'    => ['assets/js/dashboard/exceljs.min.js'],
        'sortable' => ['support/extensions/jquery/jqueryui.js'],
        'tooltips' => ['support/compiled/js/tippy.js'],
    ];

    /**
     * Which bundles each view needs.
     *
     * @var array<string, list<string>>
     */
    public array $pages = [
        'backlog/refinement'         => ['sortable'],
        'backlog/userStories'        => ['editor'],
        'dashboard/CustomReportView' => ['excel'],
        'dashboard/productDashboard' => ['charts'],
        'meeting/calendar'           => ['tooltips'],
        'sprint/createSprint'        => ['editor'],
    ];

    /**
     * Script paths for a view, without duplicates.
     *
     * @return list<string>
     */
    public function scriptsFor(?string $view): array
    {
        $scripts = [];
        foreach ($this->pages[$view] ?? [] as $bundle) {
            array_push($scripts, ...$this->libraries[$bundle]);
        }

        return array_values(array_unique($scripts));
    }
}
