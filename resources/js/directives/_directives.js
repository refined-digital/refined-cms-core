import SortableTable from './SortableTable';
import SortableRepeatableTable from './SortableRepeatableTable';
import SortableContentItem from './SortableContentItem';
import ConfirmDelete from './ConfirmDelete';
import DraggableMedia from './DraggableMedia';
import DroppableMedia from './DroppableMedia';

export function registerDirectives(app) {
  app.directive('sortable-table', SortableTable);
  app.directive('sortable-repeatable-table', SortableRepeatableTable);
  app.directive('sortable-content-item', SortableContentItem);
  app.directive('confirm-delete', ConfirmDelete);
  app.directive('draggable-media', DraggableMedia);
  app.directive('droppable-media', DroppableMedia);
}
