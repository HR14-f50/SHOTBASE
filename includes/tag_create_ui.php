<?php $tagInputName = $tagInputName ?? 'tag_ids[]'; ?>
<section class="tagCreateArea inlineTagCreate" data-tag-create="<?= BASE_URL ?>/admin/tag_create.php" data-tag-input-name="<?= h($tagInputName) ?>">
  <h3>タグを作成する</h3>
  <label for="newTagName">新しいタグ名</label>
  <div class="inlineTagControls"><input id="newTagName" name="new_tag_name" maxlength="260" placeholder="例：ナイター、外野席"><button class="button" type="button">タグを追加</button></div>
  <p class="accountHelp" role="status" aria-live="polite">「、」または「,」で区切って最大20個を追加できます。各12文字以内。追加後は合計10件まで自動で選択されます。</p>
</section>
