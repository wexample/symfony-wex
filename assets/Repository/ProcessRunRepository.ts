import AbstractApiRepository from '@wexample/js-api/Common/AbstractApiRepository';
import ProcessRun from '../Entity/ProcessRun.js';

export default class ProcessRunRepository extends AbstractApiRepository<ProcessRun> {
  static getEntityType() {
    return ProcessRun;
  }
}
