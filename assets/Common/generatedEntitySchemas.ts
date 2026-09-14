import selection from '../data/entity/selection.json';

type EntitySchema = { name: string };

export default function getGeneratedEntitySchemas(): Record<string, EntitySchema> {
  return {
    [selection.name]: selection,
  };
}
