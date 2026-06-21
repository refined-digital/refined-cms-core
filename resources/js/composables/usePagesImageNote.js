import { unref } from 'vue';

// `page` may be a ref or a plain reactive object — unref handles both.
export function usePagesImageNote(page) {
  // gets the banner dimensions note for an image field
  const getImageNote = (fieldConfig) => {
    const p = unref(page);
    const config = fieldConfig;
    let width = config.internal.width;
    let height = config.internal.height;

    if (p.id === 1) {
      width = config.home.width;
      height = config.home.height;
    }

    if (typeof config.depths !== 'undefined' && config.depths[p.depth]) {
      width = config.depths[p.depth].width;
      height = config.depths[p.depth].height;
    }

    return `Image will be resized to <strong>fit within ${width}px wide x ${height}px tall</strong>
      <br/>If you are having trouble with images, <a href="https://www.iloveimg.com/photo-editor" target="_blank">visit this page</a> to create your image.`;
  };

  return { getImageNote };
}
