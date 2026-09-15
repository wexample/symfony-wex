import AbstractApiRepository from '@wexample/js-api/Common/AbstractApiRepository';
import Process from '../Entity/Process.js';

export default class ProcessRepository extends AbstractApiRepository<Process> {
  static getEntityType() {
    return Process;
  }
}
