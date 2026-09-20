<section
  class="profileAreaA"
  aria-labelledby="profileName"
>
  <div class="profileIdentityWrap">
    <div class="profileIdentity">
      <a
        class="profileAvatarLink"
        href="<?= h(profileUrl($username, $guestPreview ? ['preview' => 'guest'] : [])) ?>"
        aria-label="<?= h($user['nickname']) ?>のプロフィールを開く"
      >
        <div class="profileAvatar">
          <?php if ($user['icon_path']): ?>
            <img
              src="<?= h(publicPhotoPath($user['icon_path'])) ?>"
              alt="<?= h($user['nickname']) ?>のアイコン"
            />
          <?php else: ?>
          <span aria-hidden="true"><?= h(mb_substr($user['nickname'], 0, 1)) ?></span>
          <?php endif; ?>
        </div>
      </a>
      <div class="profileNameBlock">
        <h2 id="profileName"><a href="<?= h(profileUrl($username, $guestPreview ? ['preview' => 'guest'] : [])) ?>"><?= h($user['nickname']) ?></a></h2>
        <p>@<?= h($user['username']) ?></p>
      </div>
      <?php if ($isOwner): ?>
        <a
          class="button profileEditButton"
          href="<?= BASE_URL ?>/admin/profile_edit.php"
        >
          プロフィールを編集
        </a>
        <a class="button profileGuestButton" href="<?= h(profileUrl($username, ['preview' => 'guest'])) ?>" target="_blank" rel="noopener">訪問者表示をプレビュー ↗</a>
      <?php endif; ?>
    </div>
    <?php if ($user['bio']): ?>
      <p class="profileBio" ><?= h(preg_replace('/\R/u', ' ', mb_substr($user['bio'], 0, 200))) ?></p>
    <?php endif; ?>
    <?php if (empty($compactSidebar)): ?><p class="profileStats">
      <span>
        <strong><?= $totalProjects ?></strong>
        プロジェクト
      </span>
      <span>
        <strong><?= $totalPhotos ?></strong>
        写真
      </span>
    </p><?php endif; ?>
  </div>
</section>
    <?php if (empty($compactSidebar)): ?><section class="sidebarSection <?= isset($sidebarProjectTags) ? 'projectTagsSection' : 'popularTagsSection' ?>">
      <?php if (isset($sidebarProjectTags)): ?>
        <h2>このプロジェクトのタグ</h2>
        <?php renderTagFilter($sidebarProjectTags, $sidebarSelectedTags, $sidebarAction, $sidebarHidden); ?>
      <?php else: ?>
        <details class="popularTagsDisclosure" data-popular-tags open>
          <summary>よく投稿するタグ</summary>
          <?php renderTagFilter($popularTags, $tagIds ?? [], BASE_URL . '/profile.php', ['username' => $username] + array_diff_key($filters, ['tags' => 1, 'tag' => 1]), '#projects'); ?>
        </details>
      <?php endif; ?>
    </section><?php endif; ?>
