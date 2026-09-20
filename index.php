<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
if (currentUserId() !== null) {
  redirect(accountHome());
}
$pageTitle = '野球の記憶を、あなたの一冊に。';
$pageClass = 'landingPage';
require_once __DIR__ . '/includes/header.php';
?>
<?php if (isset($_GET['withdrawn'])): ?><p class="notice success" role="status">退会が完了しました。ご利用ありがとうございました。</p><?php endif; ?>

<section class="landingHero">
  <div class="landingIntro">
    <p class="eyebrow">YOUR GAME. YOUR MEMORIES.</p>
    <p class="landingKicker">野球写真のための、パーソナルアーカイブ。</p>
    <h1>
      あの日の一球も、
      <br />
      スタンドの空気も。
    </h1>
    <p class="landingLead">
      撮りためた写真を、試合ごとの一冊に。
      <br />
      SHOTBASEは、あなたの「好き」を残す場所です。
    </p>
    <div class="landingActions">
      <a
        class="button primary"
        href="<?= BASE_URL ?>/register.php"
      >
        新規登録してはじめる
        <span aria-hidden="true">↗</span>
      </a>
      <a
        class="button"
        href="<?= BASE_URL ?>/login.php"
      >
        ログイン
      </a>
    </div>
    <p class="landingNote">写真をまとめて。記録を、もっと自由に。</p>
  </div>
  <div
    class="archiveIllustration"
    role="img"
    aria-label="球場のダイヤモンドを描いた、写真アルバムのイメージ"
  >
    <div class="illustrationTop">
      <span>SHOTBASE / FIELD NOTES</span>
      <span>01 — 09</span>
    </div>
    <div class="fieldDrawing">
      <div class="fieldDiamond"><i></i></div>
      <span class="fieldBall"></span>
    </div>
    <div class="illustrationBottom">
      <span>
        KEEP THE
        <br />
        WHOLE GAME.
      </span>
      <small>その瞬間の、つづきを残そう。</small>
    </div>
    <span class="illustrationCaption">アルバムのイメージ</span>
  </div>
</section>
<section
  class="landingFeatures"
  aria-labelledby="featuresTitle"
>
  <div class="landingSectionHeading">
    <p class="eyebrow">MADE FOR YOUR BASEBALL DAYS</p>
    <h2 id="featuresTitle">写真から、記録がはじまる。</h2>
    <p>投稿しきれなかった一枚にも、残しておきたい思い出がある。</p>
  </div>
  <div class="featureGrid">
    <article>
      <span class="featureNumber">01 / COLLECT</span>
      <h3>試合の思い出を、ひとまとめ。</h3>
      <p>たくさん撮った写真をプロジェクトにまとめて、撮影日やエピソードと一緒に整理。</p>
    </article>
    <article>
      <span class="featureNumber">02 / FIND</span>
      <h3>あの瞬間へ、すぐに戻れる。</h3>
      <p>年月やタグから記録をたどる。季節を越えて、好きな選手や忘れられない試合をもう一度。</p>
    </article>
    <article>
      <span class="featureNumber">03 / SHOWCASE</span>
      <h3>あなたらしい一枚を、表紙に。</h3>
      <p>お気に入りを並べたプロフィール。公開した写真を、ひとつのURLで届けられます。</p>
    </article>
  </div>
</section>
<div class="landingFooter">
  <a
    class="logo"
    href="<?= BASE_URL ?>/"
  >
    SHOTBASE
  </a>
  <span>野球を撮る。その日を残す。</span>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
