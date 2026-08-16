import _ from 'lodash';

// shared block/field helpers used by ContentBlocks (module tabs, satellite
// packages) and the page builder panel

export function uniqueId(length) {
  let result = '';
  const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  const charactersLength = characters.length;
  let counter = 0;
  while (counter < length) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
    counter += 1;
  }
  return result;
}

export function formatContent(content) {
  content.fields.forEach(field => {
    if (!field.content) {
      field.content = field.page_content_type_id === 9 ? [] : ''
    }

    // the key must exist up front for the colour picker binding to be reactive
    if (field.colour && typeof field.content_colour === 'undefined') {
      field.content_colour = '';
    }

    if (!field.id) {
      field.id = `-${_.kebabCase(field.name)}-id-${uniqueId(10)}`
    }

    if (!field.key) {
      field.key = `-${_.kebabCase(field.name)}-key-${uniqueId(10)}`
    }

    if (field.page_content_type_id === 9 && Array.isArray(field.content)) {
      field.content = field.content.map(item => {
        for (const key in item) {
          const localItem = item[key];

          if (!localItem.id) {
            localItem.id = `-${_.kebabCase(localItem.name)}-id-${uniqueId(10)}`
          }

          if (!localItem.key) {
            localItem.key = `-${_.kebabCase(localItem.name)}-key-${uniqueId(10)}`
          }
        }

        return item;
      })
    }
  })

  if (!content.id) {
    content.id = `id-${uniqueId(10)}`
  }

  if (!content.key) {
    content.key = `key-${uniqueId(10)}`
  }

  return content;
}

// mirrors how the PHP side derives content keys from field names:
// ucwords -> strip spaces -> Str::snake, e.g. 'Heading 2' => 'heading2',
// 'Mobile Image' => 'mobile_image'. lodash snakeCase differs ('heading_2')
export function phpFieldKey(name) {
  const compact = String(name)
    .replace(/\b\w/g, c => c.toUpperCase())
    .replace(/\s+/g, '');
  return compact.replace(/(.)(?=[A-Z])/g, '$1_').toLowerCase();
}

export function canShow(field, content) {
  if (!field.showOn) {
    return true;
  }

  const keys = field.showOn.split(':');
  // find the corrosponding field
  const altField = content.fields.find(item => {
    const key = _.snakeCase(item.name)
    return key === keys.at(0)
  })

  if (altField) {
    return altField.content == keys.at(1);
  }

  return true;
}
