import { ref } from 'vue';
import _ from 'lodash';

// the live preview loop shared by the full-screen workspaces: debounced draft
// renders POSTed to a preview endpoint, patched into the iframe by the shim
// (rcms:* postMessage protocol)
export function usePreviewChannel({ frame, previewUrl, getContent }) {
  const previewReady = ref(false);
  const previewError = ref(null);
  // latest-wins: responses from superseded requests are dropped
  let renderSequence = 0;

  function postToFrame(message) {
    if (frame.value && frame.value.contentWindow) {
      frame.value.contentWindow.postMessage(message, window.location.origin);
    }
  }

  async function renderPreview() {
    const sequence = ++renderSequence;

    try {
      const response = await axios.post(previewUrl(), { content: getContent() });

      if (sequence !== renderSequence) {
        return;
      }

      if (response.data.success) {
        previewError.value = null;
        postToFrame({ type: 'rcms:update', html: response.data.html });
      } else {
        // keep the last good preview, just flag it
        previewError.value = response.data.msg || 'The preview failed to render';
      }
    } catch (e) {
      if (sequence === renderSequence) {
        previewError.value = e.message;
      }
    }
  }

  const debouncedRender = _.debounce(renderPreview, 150);

  function resetPreview() {
    previewReady.value = false;
    previewError.value = null;
  }

  return {
    previewReady,
    previewError,
    postToFrame,
    renderPreview,
    debouncedRender,
    resetPreview,
  };
}
