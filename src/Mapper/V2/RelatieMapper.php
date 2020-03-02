<?php
/**
 * @author  IntoWebDevelopment <info@intowebdevelopment.nl>
 * @project SnelstartApiPHP
 */

namespace SnelstartPHP\Mapper\V2;

use function \array_map;
use Psr\Http\Message\ResponseInterface;
use SnelstartPHP\Mapper\AbstractMapper;
use SnelstartPHP\Model\EmailVersturen;
use SnelstartPHP\Model\Type as Type;
use SnelstartPHP\Model\V2 as Model;

final class RelatieMapper extends AbstractMapper
{
    public function find(ResponseInterface $response): ?Model\Relatie
    {
        $this->setResponseData($response);
        return $this->mapResponseToRelatieModel(new Model\Relatie());
    }

    public function findAll(ResponseInterface $response): \Generator
    {
        $this->setResponseData($response);
        return $this->mapManyResultsToSubMappers();
    }

    public function add(ResponseInterface $response): Model\Relatie
    {
        $this->setResponseData($response);
        return $this->mapResponseToRelatieModel(new Model\Relatie());
    }

    public function update(ResponseInterface $response): Model\Relatie
    {
        $this->setResponseData($response);
        return $this->mapResponseToRelatieModel(new Model\Relatie());
    }

    /**
     * Map the data from the response to the model.
     */
    public function mapResponseToRelatieModel(Model\Relatie $relatie, array $data = []): Model\Relatie
    {
        $data = empty($data) ? $this->responseData : $data;
        /**
         * @var Model\Relatie $relatie
         */
        $relatie = $this->mapArrayDataToModel($relatie, $data);
        $adresMapper = new AdresMapper();

        $relatie->setRelatiesoort(... array_map(static function(string $relatiesoort) {
            return new Type\Relatiesoort($relatiesoort);
        }, $data["relatiesoort"]));

        if (!empty($data["incassoSoort"])) {
            $relatie->setIncassoSoort(new Type\Incassosoort($data["incassoSoort"]));
        }

        if (!empty($data["aanmaningSoort"])) {
            $relatie->setAanmingsoort(new Type\Aanmaningsoort($data["aanmaningSoort"]));
        }

        if ($data["kredietLimiet"] !== null) {
            $relatie->setKredietLimiet($this->getMoney($data["kredietLimiet"]));
        }

        if ($data["factuurkorting"] !== null) {
            $relatie->setFactuurkorting($this->getMoney($data["factuurkorting"]));
        }

        if (!empty($data["vestigingsAdres"])) {
            $relatie->setVestigingsAdres($adresMapper->mapAdresToSnelstartObject($data["vestigingsAdres"]));
        }

        if (!empty($data["correspondentieAdres"])) {
            $relatie->setCorrespondentieAdres($adresMapper->mapAdresToSnelstartObject($data["correspondentieAdres"]));
        }

        $relatie->setOfferteEmailVersturen($this->mapEmailVersturenField($data["offerteEmailVersturen"]))
                ->setBevestigingsEmailVersturen($this->mapEmailVersturenField($data["offerteEmailVersturen"]))
                ->setFactuurEmailVersturen($this->mapEmailVersturenField($data["factuurEmailVersturen"]))
                ->setAanmaningEmailVersturen($this->mapEmailVersturenField($data["aanmaningEmailVersturen"]));

        return $relatie;
    }

    /**
     * Map all data to the EmailVersturen class (added support for subtypes).
     *
     * @param array  $emailVersturen
     * @return EmailVersturen
     */
    public function mapEmailVersturenField(array $emailVersturen): EmailVersturen
    {
        return new EmailVersturen(
            $emailVersturen["shouldSend"],
            $emailVersturen["email"],
            $emailVersturen["ccEmail"]
        );
    }

    /**
     * Map many results to the mapper.
     *
     * @return \Generator
     */
    protected function mapManyResultsToSubMappers(): \Generator
    {
        foreach ($this->responseData as $relatieData) {
            yield $this->mapResponseToRelatieModel(new Model\Relatie(), $relatieData);
        }
    }
}