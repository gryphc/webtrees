<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2017 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Fisharebest\Webtrees;

use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;

require 'includes/session.php';

$controller = new PageController;
$controller
	->restrictAccess(Auth::isAdmin())
	->setPageTitle(I18N::translate('Charts'));

$action  = Filter::post('action');
$modules = Module::getAllModulesByComponent('chart');

if ($action === 'update_mods' && Filter::checkCsrf()) {
	foreach ($modules as $module) {
		foreach (Tree::getAll() as $tree) {
			$access_level = Filter::post('access-' . $module->getName() . '-' . $tree->getTreeId(), WT_REGEX_INTEGER, $module->defaultAccessLevel());
			Database::prepare(
				"REPLACE INTO `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'chart', ?)"
			)->execute([$module->getName(), $tree->getTreeId(), $access_level]);
		}
	}

	header('Location: admin_module_charts.php');

	return;
}

$controller->pageHeader();

echo Bootstrap4::breadcrumbs([
	'admin.php'         => I18N::translate('Control panel'),
	'admin_modules.php' => I18N::translate('Module administration'),
], $controller->getPageTitle());
?>

<h1><?= $controller->getPageTitle() ?></h1>

<form method="post">
	<input type="hidden" name="action" value="update_mods">
	<?= Filter::getCsrf() ?>
	<table class="table table-bordered">
		<thead>
		<tr>
			<th class="col-xs-2"><?= I18N::translate('Chart') ?></th>
			<th class="col-xs-5"><?= I18N::translate('Description') ?></th>
			<th class="col-xs-5"><?= I18N::translate('Access level') ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($modules as $module_name => $module): ?>
			<tr>
				<td class="col-xs-2">
					<?php if ($module instanceof ModuleConfigInterface): ?>
						<a href="<?= Html::escape($module->getConfigLink()) ?>"><?= $module->getTitle() ?> <i class="fa fa-cogs"></i></a>
					<?php else: ?>
						<?= $module->getTitle() ?>
					<?php endif ?>
				</td>
				<td class="col-xs-5"><?= $module->getDescription() ?></td>
				<td class="col-xs-5">
					<table class="table">
						<tbody>
							<?php foreach (Tree::getAll() as $tree): ?>
								<tr>
									<td>
										<?= $tree->getTitleHtml() ?>
									</td>
									<td>
										<?= Bootstrap4::select(FunctionsEdit::optionsAccessLevels(), $module->getAccessLevel($tree, 'chart'), ['name' => 'access-' . $module->getName() . '-' . $tree->getTreeId()]) ?>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	<button class="btn btn-primary" type="submit">
		<i class="fa fa-check"></i>
		<?= I18N::translate('save') ?>
	</button>
</form>
