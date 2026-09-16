import AbstractApiEntity from '@wexample/js-api/Common/AbstractApiEntity';
import schema from '../data/entity/process_run.json';

export default class ProcessRun extends AbstractApiEntity {
  static readonly entityName = 'processRun';

  static retrieveEntitySchema() {
    return schema;
  }
}
