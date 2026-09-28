import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/selection.json';

export default class Selection extends AbstractApiEntity {
  static readonly entityName = 'selection';

  static retrieveEntitySchema() {
    return schema;
  }
}
