import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/process_item.json';

export default class ProcessItem extends AbstractApiEntity {
  static readonly entityName = 'processItem';

  static retrieveEntitySchema() {
    return schema;
  }
}
