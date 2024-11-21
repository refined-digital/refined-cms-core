export const PagesImageNoteMixin = {
  methods: {
    // gets the banner dimensions
    getImageNote(fieldConfig) {
      const config = fieldConfig;
      let width = config.internal.width;
      let height = config.internal.height;

      if (this.page.id === 1) {
        width = config.home.width;
        height = config.home.height;
      }

      if (typeof config.depths !== 'undefined' && config.depths[this.page.depth]) {
        width = config.depths[this.page.depth].width;
        height = config.depths[this.page.depth].height;
      }

      return `Image will be resized to <strong>fit within ${width}px wide x ${height}px tall</strong>
        <br/>If you are having trouble with images, <a href="https://www.iloveimg.com/photo-editor" target="_blank">visit this page</a> to create your image.`;
    },
  }
}
