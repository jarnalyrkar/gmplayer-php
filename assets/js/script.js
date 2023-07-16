// Constants, helpers
function removeTags(str) {
    if ((str===null) || (str===''))
        return false;
    else
        str = str.toString();
    return str.replace( /(<([^>]+)>)/ig, '');
}

async function loadJson(url) {
  let response = await fetch(url);

  if (response.status == 200) {
    let json = await response.json();
    return json;
  }

  throw new Error(response.status);
}

function activateElement(id, list) {
  const current = list.querySelector('.list__item[data-state=selected]')
  if (current && current.getAttribute('data-id') === id) return false;

  if (current) {
    current.removeAttribute('data-state')
  }
  const element = list.querySelector(`[data-id="${id}"]`)
  element.setAttribute('data-state', 'selected')

  if (list.parentElement.id === "theme") {
    getDataByTheme(id)
    loadJson(`/api/theme/set-last.php?id=${id}`)
  }
  if (list.parentElement.id === "preset") {
    const currentThemeId = document.querySelector('#theme .list__item[data-state=selected]').getAttribute('data-id')
    loadJson(`/api/preset/set-last.php?preset_id=${id}&theme_id=${currentThemeId}`)
  }
}

function setVolumeAttributes() {
  console.log("Moving volume sliders and knobs...")
}

function fadeOut(audio) {
  let fadeout = setInterval(() => {
    if (audio.volume >= 0) {
      try {
        audio.volume -= 0.025
      } catch (error) {
        audio.remove()
        clearInterval(fadeout)
        return
      }
    } else {
      audio.pause()
      return
    }
  }, 100)
}

function fadeIn(audio, targetVolume) {
  audio.volume = 0
  audio.play()
  let fadein = setInterval(() => {
    if (audio.volume <= (targetVolume / 100)) {
      audio.volume += 0.025
    } else {
      clearInterval(fadein)
    }
  }, 100)
}

function playEffect(id) {
  let audio = createAudio(id)
  audio.play()
  audio.addEventListener('ended', () => {
    audio.remove()
  })
}

// Application state

// getters & setters
function getDataByTheme(id) {
  setPresets(id)
  setTracks(id)
  setEffects(id)
}

function setPresets(theme_id) {
  const parent = document.querySelector('#preset .list')
  parent.innerHTML = ""
  loadJson(`/api/preset/get.php?id=${theme_id}`).then(data => {
    if (data.length > 0) {
      data.forEach(item => {
        const template = document.querySelector('#item')
        const clone = template.content.cloneNode(true)
        const li = clone.querySelector('.list__item')
        li.setAttribute('data-id', item.preset_id)
        if (item.current) li.setAttribute('data-state', 'selected')
        clone.querySelector('[data-action=select]').value = item.name
        parent.appendChild(clone)
      })
    }
  })
}

function setTracks(theme_id) {
  const parent = document.querySelector('#track .list')
  parent.innerHTML = ""
  loadJson(`/api/track/get.php?theme_id=${theme_id}&type=1`).then(data => {

    if (data && data.length > 0) {
      data.forEach(item => {
        const template = document.querySelector("#track-item")
        const clone = template.content.cloneNode(true)
        const li = clone.querySelector('li')
        li.setAttribute('data-id', item.track_id)
        // TODO: clone.querySelector('input[type=range]').value = ""
        li.querySelector('.track-title').innerHTML = item.name
        parent.appendChild(clone)
      })
    } else {
      parent.innerHTML = '<li class="empty">No tracks yet</li>'
    }
  })
}

function setEffects(theme_id) {
  const parent = document.querySelector('#effect .list')
  parent.innerHTML = ""
  loadJson(`/api/track/get.php?theme_id=${theme_id}&type=2`).then(data => {
    if (data && data.length > 0) {
      const keystrokes = '1234567890abcdefghijklmnopqrstuvwxyz'.split('')
      let counter = 0
      data.forEach(item => {
        const template = document.querySelector("#effect-item")
        const clone = template.content.cloneNode(true)
        const li = clone.querySelector('li')
        li.setAttribute('data-id', item.track_id)
        li.querySelector('.track-title').innerHTML = item.name
        li.setAttribute('data-keystroke', keystrokes[counter])
        li.querySelector('.keystroke').innerHTML = keystrokes[counter]
        parent.appendChild(clone)
      })
    } else {
      parent.innerHTML = '<li class="empty">No tracks yet</li>'
    }
  })
}

function setRandomSrc(track_id, audio) {
  const path = "/audio/"
  loadJson(`/api/file/random.php?id=${track_id}`).then(data => {
    audio.src = path + data.filename
  })
}

// DOM Node references
// DOM update functions
function createTheme(value, list) {
  loadJson(`/api/theme/create.php?name=${value}`)
  .then(id => {
    const template = document.querySelector('#item')
    const clone = template.content.cloneNode(true)
    const li = clone.querySelector('.list__item')
    li.setAttribute('data-id', id)
    clone.querySelector('.list__item input[type=button]').value = value
    list.append(clone)
    activateElement(id, list)
    // Add default preset
    const presetList = document.querySelector('#preset .list')
    createPreset('Default', id, presetList)

  })
}

function createPreset(value, theme_id, list) {
  let current = 0
  if (value === "Default") {
    current = 1
  }
  loadJson(`/api/preset/create.php?name=${value}&theme_id=${theme_id}&current=${current}`)
  .then(id => {
    const template = document.querySelector('#item')
    const clone = template.content.cloneNode(true)
    const li = clone.querySelector('.list__item')
    li.setAttribute('data-id', id)
    clone.querySelector('.list__item input[type=button]').value = value
    list.append(clone)
    if (current) {
      console.log(id, theme_id)
      loadJson(`/api/preset/set-last.php?preset_id=${id}&theme_id=${theme_id}`)
    }
    activateElement(id, list)
  })
}

function createTrack(value, theme_id, list) {
  // TODO (type = 1)
  // Assign keystroke
    loadJson(`/api/track/create.php?name=${value}&theme_id=${theme_id}&type_id=1`)
    .then(id => {
      const template = document.querySelector('#track-item')
      const clone = template.content.cloneNode(true)
      const li = clone.querySelector('li')
      li.setAttribute('data-id', id)
      li.querySelector('.track-title').innerHTML = value
      // TODO: set volume
      list.append(clone)
      // TODO: Add exlamation for "Add file to this track"
      const empty = list.querySelector('.empty')
      if (empty) empty.remove()
    })
}

function createEffect(value, theme_id, list) {
  loadJson(`/api/track/create.php?name=${value}&theme_id=${theme_id}&type_id=2`)
  .then(id => {
    const template = document.querySelector('#effect-item')
    const clone = template.content.cloneNode(true)
    const li = clone.querySelector('li')
    li.setAttribute('data-id', id)
    li.querySelector('.track-title').innerHTML = value
    // TODO: set volume
    list.append(clone)
    // TODO: Add exlamation for "Add file to this track"
    const empty = list.querySelector('.empty')
    if (empty) empty.remove()
  })
}

function createFile(value, track_id, list) {
  // TODO
}

function createAudio(id) {
  const audio = document.createElement('audio')
  setRandomSrc(id, audio)
  audio.setAttribute('data-id', id)
  document.body.appendChild(audio)
  return audio
}

// Event handlers
document.addEventListener('click', (ev) => {
  if (ev.target.id === "add-new") {
    const button = ev.target
    const input = button.previousElementSibling
    const value = removeTags(input.value)
    const list = button.closest("section").querySelector('.list')
    const type = list.parentElement.id
    const theme_id = document.querySelector('#theme .list__item[data-state=selected]').getAttribute('data-id')
    if (type === "theme") {
      createTheme(value, list)
    }
    if (type === "preset") {
      createPreset(value, theme_id, list)
    }
    if (type === "track") {
      createTrack(value, theme_id, list)
    }
    if (type === "effect") {
      createEffect(value, theme_id, list)
    }
    input.value = ""
  }

  if (ev.target.getAttribute('data-action') === 'select') {
    const id = ev.target.parentElement.getAttribute('data-id')
    const list = ev.target.closest("section").querySelector('.list')
    activateElement(id, list)
  }

  if (ev.target.getAttribute('data-action') === 'delete') {
    const parent = ev.target.closest("section")
    const li = ev.target.closest('li')
    const type = parent.id
    const list = parent.querySelector(".list")
    const id = li.getAttribute('data-id')
    if (type === "effect" || type === "track") {
      loadJson(`/api/track/delete.php?id=${id}`)
      li.remove()
      return
    }
    const selected = list.querySelector('[data-state=selected]').getAttribute('data-id')
    li.remove()
    if (id === selected) {
      let available = list.querySelector('.list__item').getAttribute('data-id')
      activateElement(available, list)
    }
    loadJson(`/api/${type}/delete.php?id=${id}`)
  }

  if (ev.target.getAttribute('data-action') === 'see-files') {
    console.log("display files dialog")
  }

  if (ev.target.getAttribute('data-action') === 'play') {
    // find if audio-element already exists with this file_id, if so, fade pause
    const li = ev.target.closest('li')
    const id = li.getAttribute('data-id')
    const existingAudio = document.querySelector(`audio[data-id="${id}"]`)
    const volumeBar = li.querySelector('input[type=range]')
    let targetVolume = null
    if (volumeBar) {
      targetVolume = volumeBar.value
      if (existingAudio) {
        if (existingAudio.paused) {
          fadeIn(existingAudio, targetVolume)
        } else {
          fadeOut(existingAudio)
        }
        return
      } else {
        let audio = createAudio(id)
        audio.volume = 0
        audio.play()
        fadeIn(audio, targetVolume)
        return
    }
  } else { // Effect
    let audio = createAudio(id)
    audio.volume = 0.75 // TODO: get from master sound effect slider
    audio.play()
    audio.addEventListener('ended', ev => {
      audio.remove()
    })
  }
  }
})

document.addEventListener('keydown', ev => {
  const effects = document.querySelectorAll('#effect .list li')
  const effectsLength = effects.length
  for (let i = 0; i < effectsLength; i++) {
    if (ev.code === `Digit${i+1}` || ev.code === `Numpad${i+1}`) {
      const id = effects[i].getAttribute('data-id')
      playEffect(id)
    }
    if (ev.code === effects[i].getAttribute('data-keystroke')) {
      const id = effects[i].getAttribute('data-id')
      playEffect(id)
    }
  }
})

// Event handler bindings
// Initial setup