import process from '../data/entity/process.json';
import processItem from '../data/entity/process_item.json';
import processRun from '../data/entity/process_run.json';
import selection from '../data/entity/selection.json';

type EntitySchema = { name: string };

export default function getGeneratedEntitySchemas(): Record<string, EntitySchema> {
  return {
    [process.name]: process,
    [processItem.name]: processItem,
    [processRun.name]: processRun,
    [selection.name]: selection,
  };
}
