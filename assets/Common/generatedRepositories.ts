import ProcessRepository from '../Repository/ProcessRepository.js';
import ProcessItemRepository from '../Repository/ProcessItemRepository.js';
import ProcessRunRepository from '../Repository/ProcessRunRepository.js';
import SelectionRepository from '../Repository/SelectionRepository.js';

const generatedRepositories = [ProcessRepository, ProcessItemRepository, ProcessRunRepository, SelectionRepository];

export default generatedRepositories;
