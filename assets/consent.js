/**
 * Adeldev Cookie Consent — tarteaucitron services that drive Google Consent Mode.
 * Loaded right after tarteaucitron, with the configuration in
 * window.adeldevCookieConsent (see includes/front.php).
 */
(function () {
	var config = window.adeldevCookieConsent || { services: {}, tarteaucitron: {} };
	var services = {
		ga4: {
			key: 'cc_ga4',
			type: 'analytic',
			name: 'Google Analytics (mesure d’audience)',
			uri: 'https://policies.google.com/privacy',
			consent: { analytics_storage: true }
		},
		ads: {
			key: 'cc_ads',
			type: 'ads',
			name: 'Google Ads (mesure des conversions et publicité)',
			uri: 'https://policies.google.com/technologies/ads',
			consent: { ad_storage: true, ad_user_data: true, ad_personalization: true }
		}
	};

	function update(signals, state) {
		var value = {};
		for (var k in signals) {
			value[k] = state;
		}
		gtag('consent', 'update', value);
	}

	tarteaucitron.init(config.tarteaucitron);

	Object.keys(config.services).forEach(function (id) {
		var s = services[id];
		if (!s) {
			return;
		}
		tarteaucitron.services[s.key] = {
			key: s.key,
			type: s.type,
			name: s.name,
			uri: s.uri,
			needConsent: true,
			cookies: config.services[id].cookies || [],
			js: function () { update(s.consent, 'granted'); },
			fallback: function () { update(s.consent, 'denied'); }
		};
		(tarteaucitron.job = tarteaucitron.job || []).push(s.key);
	});

	// Any link to #gerer-cookies reopens the panel, even when the hash is
	// already in the URL.
	document.addEventListener('click', function (e) {
		var link = e.target.closest && e.target.closest('a[href$="#gerer-cookies"]');
		if (link && tarteaucitron.userInterface) {
			e.preventDefault();
			tarteaucitron.userInterface.openPanel();
		}
	});

	// While the bottom bar is shown, publish its height so the theme can lift
	// its own fixed elements above it:
	//   bottom: var(--adeldev-cc-banner-height, 0px);
	// The variable is 0px once the visitor has chosen (or for other formats),
	// and body gets the class adeldev-cc-banner-visible meanwhile.
	window.addEventListener('tac.root_available', function () {
		var banner = document.getElementById('tarteaucitronAlertBig');
		if (!banner || config.tarteaucitron.orientation !== 'bottom') {
			return;
		}
		var root = document.documentElement;
		var sync = function () {
			// offsetParent is always null for fixed elements: check display.
			var height = window.getComputedStyle(banner).display !== 'none' ? banner.offsetHeight : 0;
			root.style.setProperty('--adeldev-cc-banner-height', height + 'px');
			document.body.classList.toggle('adeldev-cc-banner-visible', height > 0);
		};
		sync();
		if (window.ResizeObserver) {
			new ResizeObserver(sync).observe(banner);
		}
		new MutationObserver(sync).observe(banner, { attributes: true, attributeFilter: ['style', 'class'] });
	});
})();
