(() => {
	'use strict';

	const ready = (callback) => {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', callback, { once: true });
		} else {
			callback();
		}
	};

	ready(() => {
		const announcement = document.querySelector('[data-estatein-announcement]');
		const announcementClose = document.querySelector('[data-estatein-announcement-close]');

		try {
			if (announcement && window.sessionStorage.getItem('estateinAnnouncementDismissed') === '1') {
				announcement.hidden = true;
			}
		} catch (error) {
			// Storage can be unavailable in privacy modes; dismissal still works for this view.
		}

		if (announcement && announcementClose) {
			announcementClose.addEventListener('click', () => {
				announcement.hidden = true;
				try {
					window.sessionStorage.setItem('estateinAnnouncementDismissed', '1');
				} catch (error) {
					// No-op when storage is unavailable.
				}
			});
		}

		const menuToggle = document.querySelector('[data-estatein-menu-toggle]');
		const navigation = document.querySelector('[data-estatein-navigation]');

		const closeMenu = () => {
			if (!menuToggle || !navigation) return;
			menuToggle.setAttribute('aria-expanded', 'false');
			navigation.classList.remove('is-open');
			document.body.classList.remove('menu-open');
		};

		if (menuToggle && navigation) {
			menuToggle.addEventListener('click', () => {
				const open = menuToggle.getAttribute('aria-expanded') === 'true';
				menuToggle.setAttribute('aria-expanded', String(!open));
				navigation.classList.toggle('is-open', !open);
				document.body.classList.toggle('menu-open', !open);
			});

			navigation.addEventListener('click', (event) => {
				if (event.target.closest('a')) closeMenu();
			});

			document.addEventListener('keydown', (event) => {
				if (event.key === 'Escape') {
					closeMenu();
					menuToggle.focus();
				}
			});

			window.addEventListener('resize', () => {
				if (window.innerWidth > 1100) closeMenu();
			});
		}

		document.querySelectorAll('[data-estatein-slider], .estatein-static-slider').forEach((slider) => {
			const track = slider.querySelector('[data-estatein-track], .estatein-testimonial-track, .estatein-faq-track');
			const previous = slider.querySelector('[data-estatein-previous]');
			const next = slider.querySelector('[data-estatein-next]');
			const current = slider.querySelector('[data-estatein-current]');

			if (!track || !previous || !next || track.children.length === 0) return;

			const getStep = () => {
				const first = track.children[0];
				const styles = window.getComputedStyle(track);
				return first.getBoundingClientRect().width + (parseFloat(styles.columnGap || styles.gap) || 0);
			};

			const updateState = () => {
				const max = Math.max(0, track.scrollWidth - track.clientWidth);
				const index = Math.min(track.children.length - 1, Math.max(0, Math.round(track.scrollLeft / Math.max(1, getStep()))));
				previous.disabled = track.scrollLeft <= 2;
				next.disabled = track.scrollLeft >= max - 2;
				if (current) current.textContent = String(index + 1).padStart(2, '0');
			};

			const scroll = (direction) => {
				track.scrollBy({
					left: getStep() * direction,
					behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
				});
			};

			previous.addEventListener('click', () => scroll(-1));
			next.addEventListener('click', () => scroll(1));
			track.addEventListener('scroll', updateState, { passive: true });
			window.addEventListener('resize', updateState);
			updateState();
		});
	});
})();

