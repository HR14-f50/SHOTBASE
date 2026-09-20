<?php
// $iconMotif と $iconColor を呼び出し元から渡す共通UI。
require_once __DIR__ . '/icon_catalog.php';
$iconMotif = isset(iconMotifs()[$iconMotif ?? '']) ? $iconMotif : 'ball';
$iconColor = isset(iconColors()[$iconColor ?? '']) ? $iconColor : 'blue';
?>
<div class="iconBuilder" data-icon-builder data-base="<?= BASE_URL ?>">
  <div class="iconBuilderPreview">
    <img class="accountAvatar" data-icon-preview src="<?= h(publicPhotoPath(customIconPath($iconMotif, $iconColor))) ?>" alt="作成するアイコンのプレビュー">
    <p class="accountHelp">白い線画と好きな色を組み合わせて、あなたのアイコンに。</p>
  </div>
  <fieldset class="signupFieldset"><legend>素材を選ぶ（<?= count(iconMotifs()) ?>種類）</legend>
    <div class="motifChoices">
      <?php foreach (iconMotifs() as $key => $label): ?>
        <label><input type="radio" name="icon_motif" value="<?= h($key) ?>" <?= $key === $iconMotif ? 'checked' : '' ?>><img src="<?= BASE_URL ?>/assets/icons/motifs/<?= h($key) ?>.svg" alt=""><span><?= h($label) ?></span></label>
      <?php endforeach; ?>
    </div>
  </fieldset>
  <fieldset class="signupFieldset"><legend>背景色を選ぶ（12色）</legend>
    <div class="iconColorChoices">
      <?php foreach (iconColors() as $key => $color): ?>
        <label><input type="radio" name="icon_color" value="<?= h($key) ?>" <?= $key === $iconColor ? 'checked' : '' ?>><span style="background:<?= h($color['hex']) ?>"></span><?= h($color['name']) ?></label>
      <?php endforeach; ?>
    </div>
  </fieldset>
</div>
