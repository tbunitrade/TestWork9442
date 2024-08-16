(function (blocks, element) {
    var el = element.createElement;

    blocks.registerBlockType('storefront-child/city-weather-block', {
        title: 'City Weather Block',
        icon: 'cloud',
        category: 'widgets',
        edit: function () {
            return el('p', {}, 'City Weather Block Editor');
        },
        save: function () {
            return el('p', {}, 'City Weather Block Frontend');
        }
    });
})(
    window.wp.blocks,
    window.wp.element
);
