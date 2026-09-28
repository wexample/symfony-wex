import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/process.json';

export default class Process extends AbstractApiEntity {
  static readonly entityName = 'process';

  static retrieveEntitySchema() {
    return schema;
  }
}
