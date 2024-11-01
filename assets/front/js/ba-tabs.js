/*
 * BaTabs - Javascript Tabs
 * Copyright (c) 2010 BestAddon.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
/* global IntersectionObserver, BaTabs */

((win, doc, ba) => {
  'use strict'
  const on = (el, ev, fn) => el && ev.split(/\s+/).forEach(e => el.addEventListener(e, fn, false))
  const merge = (a, b) => Object.assign(a, b)

  function Main (obj, options) { // eslint-disable-line no-unused-vars
    if (!obj) return
    const self = this
    const defaults = { // set default options
      width: '100%',
      height: 'auto', // For Horizontal
      horizontal: false,
      speed: '500ms',
      defaultid: 0,
      event: 'click' // click, mouseenter
    }
    const opts = merge(defaults, JSON.parse(obj.getAttribute('data-options')) || options)

    const wrapper = obj.parentElement
    const tabNavs = [].slice.call(obj.children[0].children)
    const tabPanels = [].slice.call(obj.children[1].children)

    const _action = (id) => {
      [...obj.children].forEach(els => [...els.children].forEach(el => el.classList[el === els.children[id] ? 'add' : 'remove']('active')))
    }

    tabNavs.forEach(el => { // open to selected item on header click
      on(el, opts.event + ' touchstart', function (e) {
        e.preventDefault()
        _action(tabNavs.indexOf(this))
      })
    })

    self.init = () => {
      obj.className = 'ba__tabs ba__tabs' + (opts.horizontal ? '-x' : '-y')
      wrapper.style.width = opts.width
      wrapper.className = 'ba__tabs-wrapper ItemReady'
      wrapper.style.display = ''

      // start the timer for the first time
      _action(opts.defaultid % tabNavs.length)

      tabPanels.forEach(el => {
        el.style.height = opts.height
        el.style.animationDuration = opts.speed
      })
    }

    self.init()
  }

  win[ba] = Main
})(window, document, 'BaTabs');
((fn, d) => { /c/.test(d.readyState) ? fn() : d.addEventListener('DOMContentLoaded', fn) })(() => {
  [].forEach.call(document.querySelectorAll('[data-ba-tabs]'), (obj, i) => {
    window['baTabs' + i] = new BaTabs(obj)
  })
}, document)
