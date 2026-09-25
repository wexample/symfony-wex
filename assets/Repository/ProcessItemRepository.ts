import AbstractApiRepository from '@wexample/js-api/Common/AbstractApiRepository';
import ProcessItem from '../Entity/ProcessItem.js';

export default class ProcessItemRepository extends AbstractApiRepository<ProcessItem> {
  static getEntityType() {
    return ProcessItem;
  }
}
