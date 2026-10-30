/*!
 * admin-media-protect.js
 * Redraws secure (token-URL) images onto <canvas> so there is no <img> to drag,
 * "Save image as", or open; hardens all media against drag / right-click.
 * Images only — videos are streamed as blobs by the page's own player.
 */
(function () {
    'use strict';

    var SECURE = /\/secure-media\?token=|\/uploads\//i;

    function isCanvasTarget(img) {
        if (!img || img.tagName !== 'IMG') return false;
        if (img.dataset.secured === '1' || img.dataset.noCanvas === '1') return false;
        if (img.id === 'lightboxImg') return false; // reused element, harden instead
        return SECURE.test(img.getAttribute('src') || '');
    }

    function hardenImg(img) {
        img.setAttribute('draggable', 'false');
        img.oncontextmenu = function (e) { e.preventDefault(); return false; };
        img.style.webkitUserSelect = 'none';
        img.style.userSelect = 'none';
        try { img.style.webkitUserDrag = 'none'; } catch (e) {}
    }

    function drawCover(ctx, img, cw, ch) {
        var iw = img.naturalWidth || cw, ih = img.naturalHeight || ch;
        var scale = Math.max(cw / iw, ch / ih);
        var dw = iw * scale, dh = ih * scale;
        ctx.drawImage(img, (cw - dw) / 2, (ch - dh) / 2, dw, dh);
    }

    function toCanvas(img) {
        img.dataset.secured = '1';

        var build = function () {
            try {
                var rect = img.getBoundingClientRect();
                var canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth || Math.round(rect.width) || 100;
                canvas.height = img.naturalHeight || Math.round(rect.height) || 100;

                var ctx = canvas.getContext('2d');
                drawCover(ctx, img, canvas.width, canvas.height);

                var cs = window.getComputedStyle(img);
                canvas.className = img.className;
                canvas.style.width = img.style.width || (rect.width ? rect.width + 'px' : '100%');
                canvas.style.height = img.style.height || (rect.height ? rect.height + 'px' : '100%');
                canvas.style.objectFit = 'cover';
                canvas.style.borderRadius = cs.borderRadius;
                canvas.style.display = (cs.display === 'inline') ? 'inline-block' : cs.display;
                canvas.setAttribute('draggable', 'false');
                canvas.oncontextmenu = function (e) { e.preventDefault(); return false; };

                if (img.parentNode) img.parentNode.replaceChild(canvas, img);
            } catch (e) {
                hardenImg(img); // tainted / failure → at least harden it
            }
        };

        if (img.complete && img.naturalWidth) {
            build();
        } else {
            img.addEventListener('load', build, { once: true });
            img.addEventListener('error', function () {}, { once: true });
        }
    }

    function processImg(img) {
        if (img.id === 'lightboxImg' || img.dataset.noCanvas === '1') { hardenImg(img); return; }
        if (isCanvasTarget(img)) toCanvas(img);
    }

    function scan(root) {
        if (!root || !root.querySelectorAll) return;
        root.querySelectorAll('img').forEach(processImg);
    }

    function init() {
        scan(document);

        var mo = new MutationObserver(function (muts) {
            muts.forEach(function (m) {
                if (m.addedNodes) {
                    m.addedNodes.forEach(function (n) {
                        if (n.nodeType !== 1) return;
                        if (n.tagName === 'IMG') processImg(n);
                        else scan(n);
                    });
                }
                if (m.type === 'attributes' && m.target.tagName === 'IMG' && m.attributeName === 'src') {
                    if (m.target.id !== 'lightboxImg' && m.target.dataset.secured !== '1' && isCanvasTarget(m.target)) {
                        toCanvas(m.target);
                    }
                }
            });
        });
        mo.observe(document.documentElement, {
            childList: true, subtree: true, attributes: true, attributeFilter: ['src'],
        });

        // Block dragging any media element.
        document.addEventListener('dragstart', function (e) {
            var t = e.target;
            if (t && (t.tagName === 'IMG' || t.tagName === 'CANVAS' || t.tagName === 'VIDEO')) {
                e.preventDefault();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
