import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import DataTable from 'datatables.net-dt';

window.Alpine = Alpine;
window.Chart = Chart;
window.DataTable = DataTable;

if (!window.__SK_ONEPORTAL_ALPINE_STARTED__) {
	Alpine.start();
	window.__SK_ONEPORTAL_ALPINE_STARTED__ = true;
}

window.skDashboardDeps = Object.freeze({
	alpine: typeof window.Alpine !== 'undefined',
	chart: typeof window.Chart !== 'undefined',
	dataTable: typeof window.DataTable !== 'undefined',
});

window.dispatchEvent(new CustomEvent('sk:frontend-deps-ready', {
	detail: window.skDashboardDeps,
}));
