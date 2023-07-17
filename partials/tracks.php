<?php
$items = $db->get_music_by_theme($active_theme_id);
$data_type = "track";
$placeholder = "E.g Howling wind, Water drips, War cries";
?>
<section id="track">
  <header>
    <h2>Tracks</h2>
    <?php include $partials . "add-form.php"; ?>
  </header>
  <ul class="list">
    <?php if (count($items) > 0) : ?>
      <?php foreach ($items as $item) : ?>
        <li data-id="<?= $item['track_id'] ?>" data-volume="">
          <div>
            <div class="track-row">
              <div class="track-info">
                <span class="track-title"><?= $item['name']; ?></span>
                <div class="volume-bar">
                  <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/img/speaker.svg" ?>
                  <div class="volume-bar-background">
                    <input type="range" min="0" max="100" value="75">
                  </div>
                </div>
              </div>
              <div class="play-actions">
                <button class="action-button" data-action="play">&#10148;</button>
                <button class="action-button" data-action="see-files">&#128065;</button>
                <button class="action-button" data-action="delete">-</button>
              </div>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    <?php else : ?>
      <li class="empty">No tracks added yet!</li>
    <?php endif; ?>
  </ul>
  <template id="track-item">
    <li data-id="">
      <div>
        <div class="track-row">
          <div class="track-info">
            <span class="track-title"></span>
            <div class="volume-bar">
              <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/img/speaker.svg" ?>
              <div class="volume-bar-background">
                <input type="range" min="0" max="100" value="75">
              </div>
            </div>
          </div>
          <div class="play-actions">
            <button class="action-button" data-action="play" data-state="paused">&#10148;</button>
            <button class="action-button" data-action="see-files">&#128065;</button>
            <button class="action-button" data-action="delete">-</button>
          </div>
        </div>
      </div>
    </li>
  </template>
  <template id="track-files">
    <div class="dialog" class="track-dialog">
      <div class="dialog__outer">
        <div class="dialog__inner">
          <button data-action="close-dialog" aria-label="close dialog">&times;</button>
          <form>
            <input type="file" id="new-file">
            <label class="file-upload" data-action="add-file" for="new-file">Add file to track...</label>
          </form>
          <div class="dialog__files">
            <h2>Files in track</h2>
            <ul class="files"></ul>
            <template id="file">
              <li class="files__file" data-filename="" data-id="">
                <span class="file__name">filename</span>
                <button class="action-button" data-action="delete-file">-</button>
              </li>
            </template>
          </div>
        </div>
      </div>
    </div>
  </template>
</section>