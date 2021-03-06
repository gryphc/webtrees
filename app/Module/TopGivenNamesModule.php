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
namespace Fisharebest\Webtrees\Module;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Bootstrap4;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Html;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Stats;
use Fisharebest\Webtrees\View;

/**
 * Class TopGivenNamesModule
 */
class TopGivenNamesModule extends AbstractModule implements ModuleBlockInterface {
	/** {@inheritdoc} */
	public function getTitle() {
		return /* I18N: Name of a module. Top=Most common */ I18N::translate('Top given names');
	}

	/** {@inheritdoc} */
	public function getDescription() {
		return /* I18N: Description of the “Top given names” module */ I18N::translate('A list of the most popular given names.');
	}

	/**
	 * Generate the HTML content of this block.
	 *
	 * @param int      $block_id
	 * @param bool     $template
	 * @param string[] $cfg
	 *
	 * @return string
	 */
	public function getBlock($block_id, $template = true, $cfg = []) {
		global $ctype, $WT_TREE;

		$num       = $this->getBlockSetting($block_id, 'num', '10');
		$infoStyle = $this->getBlockSetting($block_id, 'infoStyle', 'table');

		foreach (['num', 'infoStyle'] as $name) {
			if (array_key_exists($name, $cfg)) {
				$$name = $cfg[$name];
			}
		}

		$stats = new Stats($WT_TREE);

		$content = '<div class="normal_inner_block">';
		//Select List or Table
		switch ($infoStyle) {
		case 'list': // Output style 1:  Simple list style. Better suited to left side of page.
			if (I18N::direction() === 'ltr') {
				$padding = 'padding-left: 15px';
			} else {
				$padding = 'padding-right: 15px';
			}
			$params = [1, $num, 'rcount'];
			// List Female names
			$totals = $stats->commonGivenFemaleTotals($params);
			if ($totals) {
				$content .= '<b>' . I18N::translate('Females') . '</b><div class="wrap" style="' . $padding . '">' . $totals . '</div><br>';
			}
			// List Male names
			$totals = $stats->commonGivenMaleTotals($params);
			if ($totals) {
				$content .= '<b>' . I18N::translate('Males') . '</b><div class="wrap" style="' . $padding . '">' . $totals . '</div><br>';
			}
			break;
		case 'table': // Style 2: Tabular format. Narrow, 2 or 3 column table, good on right side of page
			$params = [1, $num, 'rcount'];
			$content .= '<table style="margin:auto;">
						<tr>
						<td>' . $stats->commonGivenFemaleTable($params) . '</td>
						<td>' . $stats->commonGivenMaleTable($params) . '</td>';
			$content .= '</tr></table>';
			break;
		}
		$content .= '</div>';

		if ($template) {
			if ($num == 1) {
				// I18N: i.e. most popular given name.
				$title = I18N::translate('Top given name');
			} else {
				// I18N: Title for a list of the most common given names, %s is a number. Note that a separate translation exists when %s is 1
				$title = I18N::plural('Top %s given name', 'Top %s given names', $num, I18N::number($num));
			}

			if ($ctype === 'gedcom' && Auth::isManager($WT_TREE) || $ctype === 'user' && Auth::check()) {
				$config_url = Html::url('block_edit.php', ['block_id' => $block_id, 'ged' => $WT_TREE->getName()]);
			} else {
				$config_url = '';
			}

			return View::make('blocks/template', [
				'block'      => str_replace('_', '-', $this->getName()),
				'id'         => $block_id,
				'config_url' => $config_url,
				'title'      => $title,
				'content'    => $content,
			]);
		} else {
			return $content;
		}
	}

	/** {@inheritdoc} */
	public function loadAjax() {
		return false;
	}

	/** {@inheritdoc} */
	public function isUserBlock() {
		return true;
	}

	/** {@inheritdoc} */
	public function isGedcomBlock() {
		return true;
	}

	/**
	 * An HTML form to edit block settings
	 *
	 * @param int $block_id
	 */
	public function configureBlock($block_id) {
		if (Filter::postBool('save') && Filter::checkCsrf()) {
			$this->setBlockSetting($block_id, 'num', Filter::postInteger('num', 1, 10000, 10));
			$this->setBlockSetting($block_id, 'infoStyle', Filter::post('infoStyle', 'list|table', 'table'));
		}

		$num       = $this->getBlockSetting($block_id, 'num', '10');
		$infoStyle = $this->getBlockSetting($block_id, 'infoStyle', 'table');

		?>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="num">
				<?= /* I18N: ... to show in a list */ I18N::translate('Number of given names') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="num" name="num" size="2" value="<?= $num ?>">
			</div>
		</div>

		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="infoStyle">
				<?= I18N::translate('Presentation style') ?>
			</label>
			<div class="col-sm-9">
				<?= Bootstrap4::select(['list' => I18N::translate('list'), 'table' => I18N::translate('table')], $infoStyle, ['id' => 'infoStyle', 'name' => 'infoStyle']) ?>
			</div>
		</div>
		<?php
	}
}
