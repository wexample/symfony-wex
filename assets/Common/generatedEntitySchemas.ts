import process from '../data/entity/process.json';
import selection from '../data/entity/selection.json';

type EntitySchema = { name: string };

export default function getGeneratedEntitySchemas(): Record<string, EntitySchema> {
  return {
    [process.name]: process,
    [selection.name]: selection,
  };
}
