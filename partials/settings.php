<div id="settings" class="dialog">
  <div class="dialog__outer">
    <div class="dialog__inner">
      <button data-action="close-dialog" aria-label="close dialog" title="Close settings">&times;</button>
      <h2>Settings</h2>
      <div class="settings-grid">
        <div class="colorpickers">
          <h3>Interface Colors</h3>
          <div class="colorpickers-list">
            <article>
              <div id="primary-color" class="circle" style="background-color: <?= $db->HSLToRGB($primary_color); ?>"></div>
              <input type="hidden" id="primary-value" value="<?= $db->HSLToRGB($primary_color); ?>">
              <h4>Primary color</h3>
            </article>
            <article>
              <div id="accent-color" class="circle" style="background-color: <?= $db->HSLToRGB($accent_color); ?>"></div>
              <input type="hidden" id="accent-value" value="<?= $db->HSLToRGB($accent_color); ?>">
              <h4>Accent color</h3>
            </article>
          </div>
          <button data-action="reset-theme">Set Default Colors</button>
        </div>
        <div class="setting" data-section="background-image">
          <label for="bg-img-url">Background image URL</label>
          <input id="bg-img-url" type="text" value="<?= $background_image ?>">
          <p class="explain">Tip: You can also move an image into the app directory and rename it &ldquo;bg.jpeg&rdquo;, and that will be used if no URL is present.</p>
        </div>
        <div class="setting">
          <label>Dominant Colors</label>
          <p class="explain">Click to use background image as base for Interface Colors</p>
          <button data-action="dominant-theme">Set Interface Colors</button>
        </div>
        <div class="setting" data-section="font-setting">
          <label for="font-setting">Google font name</label>
          <input id="font-setting" type="text" value="<?= $font ?? '' ?>">
          <p class="explain">Add the name for any <a href="https://fonts.google.com/">google font</a> here.
            It needs to match exactly, so copy and paste may be a good idea. Click outside the textbox to see the difference.</p>
        </div>
      </div>
    </div>
  </div>
</div>