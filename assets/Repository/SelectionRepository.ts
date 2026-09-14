import AbstractApiRepository from '@wexample/js-api/Common/AbstractApiRepository';
import Selection from '../Entity/Selection.js';

export default class SelectionRepository extends AbstractApiRepository<Selection> {
  static getEntityType() {
    return Selection;
  }
}
