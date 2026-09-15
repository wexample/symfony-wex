import AbstractApiEntity from '@wexample/js-api/Common/AbstractApiEntity';
import schema from '../data/entity/process.json';

export default class Process extends AbstractApiEntity {
  static readonly entityName = 'process';

  static retrieveEntitySchema() {
    return schema;
  }
}
