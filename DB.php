<?php
define('TYPE', [
  'music' => 1,
  'effect' => 2
]);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class DB {
  public $pdo;

  function __construct() {
    $this->pdo = new PDO("sqlite:" . $_SERVER['DOCUMENT_ROOT'] . "/dmplayer.db");
  }

  // Theme
  // Create
  public function create_theme($name) {
    $query = "INSERT INTO theme (name) VALUES(:name)";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindValue('name', $name);
    $stmt->execute();
    return $this->pdo->lastInsertId();
  }
  // Read
  public function get_themes() {
    $query = $this->pdo->query("SELECT theme_id, name FROM theme");
    $themes = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
      $themes[] = $row;
    }
    return $themes;
  }
  public function get_last_active_theme() {
    return $this->get_setting_by_name('last_theme');
  }
  // Update
  public function update_theme($id, $newName) {}
  public function update_last_theme($id) {
    $sql = "UPDATE settings SET value = :id WHERE option = \"last_theme\"";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->execute();
  }
  // Delete
  public function delete_theme($id) {
    $this->pdo->query("DELETE FROM theme_track WHERE theme_id = $id;");
    $this->pdo->query("DELETE FROM theme_preset WHERE theme_id = $id;");
    $this->pdo->query("DELETE FROM theme WHERE theme_id = $id;");
    return ["message" => $id . " is deleted"];
  }

  // Preset
  // Create
  public function create_preset($name, $theme_id, $current = 0) {
    // create preset
    $sql = "INSERT INTO preset (name) VALUES(:name)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->execute();
    $preset_id = $this->pdo->lastInsertId();

    // add preset to theme
    $sql = "INSERT INTO theme_preset (theme_id, preset_id, current) VALUES(:theme_id, :preset_id, :current)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':theme_id', $theme_id);
    $stmt->bindValue(':preset_id', $preset_id);
    $stmt->bindValue(':current', $current);
    $stmt->execute();

    return $preset_id;
  }
  // Read
  public function get_presets_by_theme($id) {
    $query = $this->pdo->prepare("
      SELECT preset_id, name, current
      FROM theme_preset
      INNER JOIN preset USING(preset_id)
      WHERE theme_id = :theme_id
    ");
    $query->bindValue(':theme_id', $id);
    $query->execute();
    $results = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
      $results[] = $row;
    }
    return $results;
  }

  public function get_last_active_preset($theme_id) {
    $query = $this->pdo->prepare("
      SELECT preset_id
      FROM theme_preset
      WHERE theme_id = :theme_id
      AND current = 1
    ");
    $query->bindValue(":theme_id", $theme_id);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_NUM);
    if (is_array($result) && array_key_exists(0, $result)) {
      return $result[0];
    } else {
      return false;
    }

  }

  // Update
  public function update_preset($id, $newName) {}
  public function update_last_preset($preset_id, $theme_id) {
    // remove old
    $sql = "
      UPDATE theme_preset
      SET current = 0
      WHERE current = 1
      AND theme_id = :theme_id
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':theme_id', $theme_id);
    $stmt->execute();

    // set new
    $sql = "
      UPDATE theme_preset
      SET current = 1
      WHERE theme_id = :theme_id
      AND preset_id = :preset_id
      ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':theme_id', $theme_id);
    $stmt->bindValue(':preset_id', $preset_id);
    $stmt->execute();

    $sql = "
      SELECT preset_id
      FROM theme_preset
      ORDER BY preset_id
      DESC LIMIT 1;
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetch();
  }
  // Delete
  public function delete_preset($id) {
    $this->pdo->query("DELETE FROM theme_preset WHERE preset_id = $id;");
    $this->pdo->query("DELETE FROM preset WHERE preset_id = $id;");

    return ["message" => $id . " is deleted"];
  }
  public function delete_presets_by_theme($id) {
    $presets = $this->get_presets_by_theme($id);
    foreach ($presets as $preset) {
      $this->delete_preset($preset['preset_id']);
    }
  }

  // Track
  // Create
  public function create_track($name, $theme_id, $type_id) {
    // create track
    $sql = "INSERT INTO track (name, type_id) VALUES(:name, :type_id)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':type_id', $type_id);
    $stmt->execute();
    $track_id = $this->pdo->lastInsertId();

    // add empty entry to track_file
    $sql = "INSERT INTO track_file (track_id) VALUES(:track_id)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':track_id', $track_id);
    $stmt->execute();
    $track_file_id = $this->pdo->lastInsertId();

    // add track to theme
    $sql = "INSERT INTO theme_track (theme_id, track_file_id) VALUES(:theme_id, :track_file_id)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':theme_id', $theme_id);
    $stmt->bindValue(':track_file_id', $track_file_id);
    $stmt->execute();

    return $track_id;
  }
  // Read
  private function get_tracks_by_theme($theme_id, $type_id) {
    $query = $this->pdo->prepare("
      SELECT track_id, name
      FROM theme_track, track_file
      INNER JOIN track USING (track_id)
      WHERE theme_id = :theme_id
      AND type_id = :type_id
      GROUP BY track_id, type_id
    ");
    $query->bindValue(':theme_id', $theme_id);
    $query->bindValue(':type_id', $type_id);
    $query->execute();
    $results = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
      $results[] = $row;
    }
    return $results;
  }

  public function get_music_by_theme($theme_id) {
    return $this->get_tracks_by_theme($theme_id, TYPE['music']);
  }

  public function get_effects_by_theme($theme_id) {
    return $this->get_tracks_by_theme($theme_id, TYPE['effect']);
  }

  // Update
  public function update_track($id, $newName) {}
  // Delete
  public function delete_track($id) {
    $sql = $this->pdo->prepare("SELECT track_file_id FROM track_file WHERE track_id = $id");
    $sql->execute();
    $track_file_id = $sql->fetch()['track_file_id'];

    $this->pdo->query("DELETE FROM theme_track WHERE track_file_id = $track_file_id");
    $this->pdo->query("DELETE FROM track_file WHERE track_id = $id");
    $this->pdo->query("DELETE FROM track WHERE track_id = $id");

    return ["message" => $id . " is deleted"];
  }

  // File
  // Create
  public function create_file($filename, $track_id) {}
  // Read
  public function get_files_by_track($track_id) {
    $query = $this->pdo->prepare("
      SELECT filename
      FROM file
      INNER JOIN track_file USING(file_id)
      WHERE track_id = :track_id
    ");
    $query->bindValue(':track_id', $track_id);
    $query->execute();
    $results = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
      $results[] = $row;
    }
    return $results;
  }
  // Update
  public function update_file($id, $newName) {}
  // Delete
  public function delete_file($id) {}

  // Settings
  private function get_setting_by_name($option) {
    $query = $this->pdo->query("SELECT value FROM settings WHERE option = \"$option\"");
    $query->execute();
    return $query->fetch()['value'];
  }

}