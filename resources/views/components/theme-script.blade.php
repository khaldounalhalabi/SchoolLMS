{{--
    Pre-paint theme resolution. MUST be rendered in <head> BEFORE @vite so
    data-theme is stamped on <html> before the first paint — otherwise the
    light palette flashes before the dark one applies.

    Deliberately inline and un-bundled: a Vite-served module is deferred and
    would run after paint, which is exactly the flash this prevents.

    Storage contract: localStorage['theme'] ∈ {'light','dark','system'}.
    Absent or unrecognised is treated as 'system'.
--}}
<script>
    (function () {
        var KEY = 'theme';
        var root = document.documentElement;

        function resolve(pref) {
            if (pref === 'light' || pref === 'dark') return pref;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        var pref;
        try { pref = localStorage.getItem(KEY); } catch (e) { pref = null; }
        if (pref !== 'light' && pref !== 'dark' && pref !== 'system') pref = 'system';

        root.setAttribute('data-theme', resolve(pref));
        root.setAttribute('data-theme-pref', pref);
    })();
</script>
