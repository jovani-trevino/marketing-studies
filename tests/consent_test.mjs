import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../blog/public/static/app-ui.js', import.meta.url), 'utf8');

// The DOM adapter records insertion and script activation requests. It loads
// the production script without reproducing any consent decision logic.
function page({ stored = null, storageBlocked = false, bannerEnabled = true } = {}) {
    const storage = new Map(stored === null ? [] : [['gb-cookie-consent', stored]]);
    const listeners = new Map();
    const inserted = []; const scripts = []; const slots = [];
    const state = { shown: 0, hidden: 0, reloads: 0, inserted, scripts, storage };
    const classes = { add() {}, toggle() {} };
    const button = (action) => ({
        click: null,
        getAttribute: () => action,
        addEventListener(_name, handler) { this.click = handler; }
    });
    const accept = button('accept'); const essential = button('essential'); const preferences = button('settings');
    const modal = { querySelectorAll: () => [essential, accept] };
    const entries = ['global-tracker', 'ad-slot'].map((name) => {
        const slot = { hidden: true }; slots.push(slot);
        return {
            textContent: JSON.stringify({ html: '<script src="https://optional.invalid/' + name + '.js"></script><img src="https://optional.invalid/pixel">' }),
            dataset: {}, closest: () => slot,
            before(fragment) { inserted.push(fragment.html); }
        };
    });
    const root = { scrollTop: 0, scrollHeight: 1000, attributes: new Map(), setAttribute(key, value) { this.attributes.set(key, value); }, getAttribute(key) { return this.attributes.get(key); } };
    const document = {
        documentElement: root, body: { classList: classes }, title: 'Fixture',
        getElementById(id) { return id === 'cookie-consent-modal' ? modal : null; },
        querySelector() { return null; },
        querySelectorAll(selector) {
            if (selector.includes('data-ms-consent-code')) return entries;
            if (selector === '[data-cookie-settings]') return [preferences];
            return [];
        },
        addEventListener(name, handler) { listeners.set(name, handler); }, dispatchEvent() {},
        createElement(tag) {
            if (tag === 'template') {
                const template = { content: { html: '', querySelectorAll() { return this.scripts; } } };
                Object.defineProperty(template, 'innerHTML', { set(html) {
                    this.content.html = html;
                    const src = html.match(/src="([^"]+)"/)[1];
                    this.content.scripts = [{
                        attributes: [{ name: 'src', value: src }], textContent: '', hasAttribute: () => false,
                        replaceWith(script) { scripts.push(script.src); script.events.load?.(); }
                    }];
                } });
                return template;
            }
            if (tag === 'script') return {
                events: {}, src: '', textContent: '',
                setAttribute(name, value) { this[name] = value; },
                addEventListener(name, handler) { this.events[name] = handler; }
            };
            throw new Error('Unexpected element ' + tag);
        }
    };
    const window = {
        GB_RUNTIME: { cookieConsentEnabled: bannerEnabled },
        matchMedia: (query) => ({ matches: query.includes('reduced-motion'), addEventListener() {} }),
        scrollY: 0, innerHeight: 800, addEventListener() {}, requestAnimationFrame: (callback) => callback(),
        setTimeout: (callback) => callback(),
        location: { reload() { state.reloads++; } },
        bootstrap: { Modal: class { show() { state.shown++; } hide() { state.hidden++; } } }
    };
    const localStorage = {
        getItem(key) { if (storageBlocked) throw new Error('Blocked'); return storage.get(key) ?? null; },
        setItem(key, value) { if (storageBlocked) throw new Error('Blocked'); storage.set(key, value); }
    };
    vm.runInNewContext(source, { window, document, localStorage, navigator: {}, CustomEvent: class {}, URLSearchParams });
    listeners.get('DOMContentLoaded')();
    return { ...state, state, accept, essential, preferences, slots };
}

const settle = () => new Promise(resolve => setImmediate(resolve));

test('no choice or essential-only choice never inserts optional code or tracking pixels', async () => {
    for (const stored of [null, '{"choice":"essential"}', '{broken-json}', '{"choice":"unexpected"}']) {
        const browser = page({ stored });
        await settle();
        assert.equal(browser.inserted.length, 0);
        assert.equal(browser.scripts.length, 0);
        browser.essential.click();
        await settle();
        assert.equal(browser.inserted.length, 0);
        assert.equal(JSON.parse(browser.storage.get('gb-cookie-consent')).choice, 'essential');
    }
});

test('explicit acceptance activates the global script and ad slot exactly once', async () => {
    const browser = page();
    browser.accept.click();
    await settle();
    assert.equal(browser.inserted.length, 2);
    assert.equal(browser.scripts.length, 2);
    assert.ok(browser.slots.every(slot => slot.hidden === false));
    browser.accept.click();
    await settle();
    assert.equal(browser.scripts.length, 2);
});

test('withdrawing prior acceptance stores essentials and reloads before the next page', async () => {
    const browser = page({ stored: '{"choice":"accept"}' });
    await settle();
    assert.equal(browser.scripts.length, 2);
    browser.preferences.click();
    assert.equal(browser.state.shown, 1);
    browser.essential.click();
    assert.equal(browser.state.reloads, 1);
    const nextPage = page({ stored: browser.storage.get('gb-cookie-consent') });
    await settle();
    assert.equal(nextPage.inserted.length, 0);
});

test('blocked storage and a disabled banner both default to no optional execution', async () => {
    const restricted = page({ storageBlocked: true });
    restricted.essential.click();
    const disabled = page({ bannerEnabled: false });
    await settle();
    assert.equal(restricted.inserted.length, 0);
    assert.equal(disabled.inserted.length, 0);
});
