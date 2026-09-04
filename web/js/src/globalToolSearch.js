export default function handleGlobalToolSearch() {
	const form = document.querySelector('[data-orbix-tool-search]');
	const input = document.querySelector('#orbix-global-search-input');
	const options = [...document.querySelectorAll('#orbix-tool-search-options option')];

	if (!form || !input || options.length === 0) {
		return;
	}

	const findDestination = () => {
		const query = input.value.trim().toLocaleLowerCase();
		if (!query) {
			return null;
		}

		return (
			options.find((option) => option.value.toLocaleLowerCase() === query) ||
			options.find((option) => option.value.toLocaleLowerCase().includes(query))
		);
	};

	form.addEventListener('submit', (event) => {
		event.preventDefault();
		const destination = findDestination();
		if (destination?.dataset.orbixUrl) {
			window.location.assign(destination.dataset.orbixUrl);
		}
	});

	input.addEventListener('change', () => {
		const destination = findDestination();
		if (destination?.dataset.orbixUrl) {
			window.location.assign(destination.dataset.orbixUrl);
		}
	});

	document.addEventListener('keydown', (event) => {
		if (
			event.key === '/' &&
			!event.ctrlKey &&
			!event.metaKey &&
			!event.altKey &&
			!['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)
		) {
			event.preventDefault();
			input.focus();
		}
	});
}
