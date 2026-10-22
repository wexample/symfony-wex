import AbstractApiRepository from '@wexample/js-api-entity/Common/AbstractApiRepository';
import Process from '../Entity/Process.js';

export default class ProcessRepository extends AbstractApiRepository<Process> {
  static getEntityType() {
    return Process;
  }
}
