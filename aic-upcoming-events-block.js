const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;

registerBlockType('aic-upcoming-events/events-block', {
		title: 'AIC Upcoming Events',
		icon: 'calendar-alt',
		category: 'common',
		edit: function( props ) {
				return createElement( 'div', { className: props.className },
						'The next 5 events from the AIC API will be displayed in your post when published.'
				);
		},
		save: function() {
				return null;
		}
});
